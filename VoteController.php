<?php
require_once 'DBController.php';
require_once 'UserController.php';
require_once 'models/Badge.php';

class VoteController {
    private $db;
    private $userController;
    
    public function __construct($db) {
        $this->db = $db;
        $this->userController = new UserController($db);
    }
    
    public function vote($contentId, $contentType, $userId, $voteType) {
        $contentId = (int)$contentId;
        $userId = (int)$userId;
        $contentType = $this->db->connection->real_escape_string($contentType);
        
        // Validate vote type
        if ($voteType !== 'up' && $voteType !== 'down') {
            return [
                'success' => false,
                'message' => 'نوع التصويت غير صالح'
            ];
        }
        
        // Check if user has permission to vote (prevent self-voting)
        if ($contentType === 'question') {
            $query = "SELECT user_id FROM questions WHERE id = $contentId";
        } else if ($contentType === 'answer') {
            $query = "SELECT user_id FROM answers WHERE id = $contentId";
        } else {
            return [
                'success' => false,
                'message' => 'نوع المحتوى غير صالح'
            ];
        }
        
        $result = $this->db->select($query);
        if (empty($result)) {
            return [
                'success' => false,
                'message' => 'المحتوى غير موجود'
            ];
        }
        
        $authorId = $result[0]['user_id'];
        if ($authorId == $userId) {
            return [
                'success' => false,
                'message' => 'لا يمكنك التصويت على المحتوى الخاص بك'
            ];
        }
        
        // Check if user has already voted
        $query = "SELECT * FROM votes WHERE content_id = $contentId AND content_type = '$contentType' AND user_id = $userId";
        $existingVote = $this->db->select($query);
        
        $isUpvote = $voteType === 'up';
        $voteValue = $isUpvote ? 1 : -1;
        
        if (empty($existingVote)) {
            // Insert new vote
            $query = "INSERT INTO votes (content_id, content_type, user_id, vote_value, created_at) 
                      VALUES ($contentId, '$contentType', $userId, $voteValue, NOW())";
            $result = $this->db->insert($query);
            
            if ($result) {
                $this->updateContentScore($contentId, $contentType, $voteValue);
                $this->updateUserReputation($contentId, $contentType, $voteValue);
                
                return [
                    'success' => true,
                    'message' => $isUpvote ? 'تم التصويت الإيجابي بنجاح' : 'تم التصويت السلبي بنجاح'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'حدث خطأ أثناء التصويت'
                ];
            }
        } else {
            $existingVoteValue = $existingVote[0]['vote_value'];
            
            // If user is voting the same way, remove the vote
            if ($existingVoteValue == $voteValue) {
                $query = "DELETE FROM votes WHERE content_id = $contentId AND content_type = '$contentType' AND user_id = $userId";
                $result = $this->db->delete($query);
                
                if ($result) {
                    $this->updateContentScore($contentId, $contentType, -$voteValue);
                    $this->updateUserReputation($contentId, $contentType, -$voteValue);
                    
                    return [
                        'success' => true,
                        'message' => 'تم إلغاء التصويت'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'حدث خطأ أثناء إلغاء التصويت'
                    ];
                }
            } else {
                // If user is changing vote, update it
                $query = "UPDATE votes SET vote_value = $voteValue WHERE content_id = $contentId AND content_type = '$contentType' AND user_id = $userId";
                $result = $this->db->update($query);
                
                if ($result) {
                    // Double the effect since we're switching from -1 to 1 or vice versa
                    $this->updateContentScore($contentId, $contentType, 2 * $voteValue);
                    $this->updateUserReputation($contentId, $contentType, 2 * $voteValue);
                    
                    return [
                        'success' => true,
                        'message' => $isUpvote ? 'تم تغيير التصويت إلى إيجابي' : 'تم تغيير التصويت إلى سلبي'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'حدث خطأ أثناء تغيير التصويت'
                    ];
                }
            }
        }
    }
    
    private function updateContentScore($contentId, $contentType, $voteValue) {
        $contentId = (int)$contentId;
        $voteValue = (int)$voteValue;
        $table = $contentType === 'question' ? 'questions' : 'answers';
        
        $query = "UPDATE $table SET score = score + $voteValue WHERE id = $contentId";
        $this->db->update($query);
    }
    
    private function updateUserReputation($contentId, $contentType, $voteValue) {
        $contentId = (int)$contentId;
        $voteValue = (int)$voteValue;
        
        // Get author ID
        $table = $contentType === 'question' ? 'questions' : 'answers';
        $query = "SELECT user_id FROM $table WHERE id = $contentId";
        $result = $this->db->select($query);
        
        if (!empty($result)) {
            $authorId = $result[0]['user_id'];
            
            // Adjust reputation value based on content type
            $reputationChange = $contentType === 'question' ? ($voteValue * 5) : ($voteValue * 10);
            
            // Update user reputation
            $query = "UPDATE users SET reputation = reputation + $reputationChange WHERE id = $authorId";
            $this->db->update($query);
            
            // Check if user reached a badge threshold and notify them
            if ($voteValue > 0) {
                $userRep = $this->userController->getUserReputation($authorId);
                
                if ($userRep == Badge::SILVER_THRESHOLD || 
                    $userRep == Badge::BRONZE_THRESHOLD || 
                    $userRep == Badge::GOLD_THRESHOLD) {
                    // In a real app, you would send a notification here
                }
            }
        }
    }
    
    public function getVoteStatus($contentId, $contentType, $userId) {
        if (!$userId) {
            return 0;
        }
        
        $contentId = (int)$contentId;
        $userId = (int)$userId;
        $contentType = $this->db->connection->real_escape_string($contentType);
        
        $query = "SELECT vote_value FROM votes WHERE content_id = $contentId AND content_type = '$contentType' AND user_id = $userId";
        $result = $this->db->select($query);
        
        if (!empty($result)) {
            return $result[0]['vote_value'];
        }
        
        return 0;
    }
    
    public function getVoteCounts($contentId, $contentType) {
        $contentId = (int)$contentId;
        $contentType = $this->db->connection->real_escape_string($contentType);
        
        $query = "SELECT 
                 SUM(CASE WHEN vote_value = 1 THEN 1 ELSE 0 END) as upvotes,
                 SUM(CASE WHEN vote_value = -1 THEN 1 ELSE 0 END) as downvotes
                 FROM votes 
                 WHERE content_id = $contentId AND content_type = '$contentType'";
        $result = $this->db->select($query);
        
        if (!empty($result)) {
            return [
                'upvotes' => $result[0]['upvotes'] ? $result[0]['upvotes'] : 0,
                'downvotes' => $result[0]['downvotes'] ? $result[0]['downvotes'] : 0
            ];
        }
        
        return [
            'upvotes' => 0,
            'downvotes' => 0
        ];
    }
}
?>
