
<head>
<?php if (isset($_SESSION['language']) && $_SESSION['language'] == 'en'): ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <?php else: ?>
        <!-- Bootstrap RTL CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <?php endif; ?>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<?php
require_once('../../../../controllers/DBController.php');
require_once '../../layouts/head.php';
// Make sure the database connection is available
if (!isset($db)) {
    // Create database connection if not already available
    $db = new DBController();
    $db->openConnection();
}
?>

<div class="row">
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>آخر الأسئلة</h2>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="index.php?page=ask-question" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-1"></i> اطرح سؤالاً
                </a>
            <?php endif; ?>
        </div>
        
        <?php if (isset($questions) && !empty($questions)): ?>
            <?php foreach ($questions as $question): ?>
                <div class="card question-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 text-center">
                                <div class="vote-count"><?php echo isset($question['upvotes']) ? $question['upvotes'] : 0; ?></div>
                                <div class="small text-muted">تصويت</div>
                                <div class="answer-count mt-2"><?php echo $question['answer_count']; ?></div>
                                <div class="small text-muted">إجابة</div>
                            </div>
                            <div class="col-md-10">
                                <h5 class="card-title">
                                    <a href="index.php?page=view-question&id=<?php echo $question['id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($question['title']); ?>
                                    </a>
                                </h5>
                                <p class="card-text"><?php echo substr(htmlspecialchars($question['content']), 0, 200); ?>...</p>
                                <div class="d-flex flex-wrap">
                                    <?php
                                    // Get tags for this question
                                    $query = "SELECT t.* FROM tags t 
                                              INNER JOIN question_tags qt ON t.id = qt.tag_id 
                                              WHERE qt.question_id = " . $question['id'];
                                    $tags = $db->select($query);
                                    
                                    if (!empty($tags)) {
                                        foreach ($tags as $tag) {
                                            echo '<span class="tag">' . htmlspecialchars($tag['name']) . '</span>';
                                        }
                                    }
                                    ?>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <small class="text-muted">سُئل <?php echo date('d M Y', strtotime($question['created_at'])); ?></small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <img src="https://via.placeholder.com/32" class="rounded-circle me-2" alt="User Avatar">
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($question['username']); ?></div>
                                            <span class="reputation"><?php echo $question['reputation']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">لا توجد أسئلة حتى الآن. كن أول من يطرح سؤالاً!</div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">التصنيفات الشائعة</h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap">
                    <?php
                    // Get popular tags
                    $query = "SELECT t.*, COUNT(qt.question_id) as question_count 
                              FROM tags t 
                              INNER JOIN question_tags qt ON t.id = qt.tag_id 
                              GROUP BY t.id 
                              ORDER BY question_count DESC 
                              LIMIT 10";
                    $popularTags = $db->select($query);
                    
                    if (!empty($popularTags)) {
                        foreach ($popularTags as $tag) {
                            echo '<a href="index.php?page=tag&id=' . $tag['id'] . '" class="tag text-decoration-none">' . 
                                    htmlspecialchars($tag['name']) . 
                                    ' <span class="badge bg-secondary rounded-pill">' . $tag['question_count'] . '</span>' . 
                                 '</a>';
                        }
                    } else {
                        echo '<p class="text-muted">لا توجد تصنيفات بعد</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">المستخدمون النشطون</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php
                    // Get active users
                    $query = "SELECT u.id, u.username, u.reputation 
                              FROM users u 
                              ORDER BY u.reputation DESC 
                              LIMIT 5";
                    $activeUsers = $db->select($query);
                    
                    if (!empty($activeUsers)) {
                        foreach ($activeUsers as $user) {
                            echo '<li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <img src="https://via.placeholder.com/32" class="rounded-circle me-2" alt="User Avatar">
                                        <span>' . htmlspecialchars($user['username']) . '</span>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">' . $user['reputation'] . '</span>
                                  </li>';
                        }
                    } else {
                        echo '<li class="list-group-item">لا يوجد مستخدمون نشطون بعد</li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<script src="../assets/js/main.js"></script>
<?php
require_once '../..//layouts/footer.php';
?>