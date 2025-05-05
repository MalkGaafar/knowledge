<?php
require_once '../controllers/userContoller';
// Make sure the database connection is established
if (!isset($db)) {
    // Create database connection if not already available
    $db = new DBController();
    $db->openConnection();
}

// Get the current user's information if the user variable isn't set
if (!isset($user) && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $userController = new UserController($db);
    $user = $userController->getUserById($userId);
} else if (!isset($user)) {
    // If there's no user_id in session, redirect to login
    header('Location: index.php?page=login');
    exit;
}

// Make sure $user is valid before continuing
if (!$user || !is_array($user)) {
    echo '<div class="alert alert-danger">User information could not be loaded.</div>';
    exit;
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <img src="https://via.placeholder.com/150" class="rounded-circle img-fluid mb-3" alt="Profile Image">
                <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                <p class="text-muted">عضو منذ <?php echo date('d M Y', strtotime($user['created_at'])); ?></p>
                <div class="d-flex justify-content-center">
                    <span class="badge bg-success me-2">
                        <i class="fas fa-star me-1"></i>
                        <?php echo $user['reputation']; ?> نقطة سمعة
                    </span>
                    <?php if ($user['reputation'] >= 200): ?>
                        <span class="badge bg-info">خبير</span>
                    <?php elseif ($user['reputation'] >= 100): ?>
                        <span class="badge bg-primary">متقدم</span>
                    <?php elseif ($user['reputation'] >= 50): ?>
                        <span class="badge bg-secondary">نشط</span>
                    <?php else: ?>
                        <span class="badge bg-light text-dark">مبتدئ</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">الشارات</h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap">
                    <?php if ($user['reputation'] >= 50): ?>
                        <div class="badge-item m-1 text-center">
                            <i class="fas fa-comment-dots fa-2x text-success"></i>
                            <p class="small mb-0">مشارك نشط</p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($user['reputation'] >= 100): ?>
                        <div class="badge-item m-1 text-center">
                            <i class="fas fa-brain fa-2x text-primary"></i>
                            <p class="small mb-0">خبير</p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($user['reputation'] >= 200): ?>
                        <div class="badge-item m-1 text-center">
                            <i class="fas fa-award fa-2x text-warning"></i>
                            <p class="small mb-0">معلم</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <ul class="nav nav-tabs" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="questions-tab" data-bs-toggle="tab" data-bs-target="#questions" type="button" role="tab" aria-controls="questions" aria-selected="true">أسئلتي</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="answers-tab" data-bs-toggle="tab" data-bs-target="#answers" type="button" role="tab" aria-controls="answers" aria-selected="false">إجاباتي</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="saved-tab" data-bs-toggle="tab" data-bs-target="#saved" type="button" role="tab" aria-controls="saved" aria-selected="false">المحفوظات</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab" aria-controls="settings" aria-selected="false">الإعدادات</button>
            </li>
        </ul>
        
        <div class="tab-content p-3 border border-top-0 rounded-bottom" id="profileTabsContent">
            <div class="tab-pane fade show active" id="questions" role="tabpanel" aria-labelledby="questions-tab">
                <?php
                // Get user's questions
                if (isset($user['id'])) {
                    $query = "SELECT * FROM questions WHERE user_id = " . $user['id'] . " ORDER BY created_at DESC";
                    $userQuestions = $db->select($query);
                    
                    if (!empty($userQuestions)) {
                        foreach ($userQuestions as $question) {
                            echo '<div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a href="index.php?page=view-question&id=' . $question['id'] . '">
                                                ' . htmlspecialchars($question['title']) . '
                                            </a>
                                        </h5>
                                        <p class="card-text">' . substr(htmlspecialchars($question['content']), 0, 150) . '...</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">' . date('d M Y', strtotime($question['created_at'])) . '</small>
                                        </div>
                                    </div>
                                </div>';
                        }
                    } else {
                        echo '<div class="alert alert-info">لم تقم بطرح أي أسئلة بعد.</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger">لا يمكن تحميل الأسئلة.</div>';
                }
                ?>
            </div>
            
            <div class="tab-pane fade" id="answers" role="tabpanel" aria-labelledby="answers-tab">
                <?php
                // Get user's answers
                if (isset($user['id'])) {
                    $query = "SELECT a.*, q.title as question_title, q.id as question_id 
                              FROM answers a 
                              JOIN questions q ON a.question_id = q.id 
                              WHERE a.user_id = " . $user['id'] . " 
                              ORDER BY a.created_at DESC";
                    $userAnswers = $db->select($query);
                    
                    if (!empty($userAnswers)) {
                        foreach ($userAnswers as $answer) {
                            echo '<div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">
                                            إجابة على: 
                                            <a href="index.php?page=view-question&id=' . $answer['question_id'] . '">
                                                ' . htmlspecialchars($answer['question_title']) . '
                                            </a>
                                        </h6>
                                        <p class="card-text">' . substr(htmlspecialchars($answer['content']), 0, 150) . '...</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">' . date('d M Y', strtotime($answer['created_at'])) . '</small>
                                            ' . ($answer['is_accepted'] ? '<span class="badge bg-success">إجابة مقبولة</span>' : '') . '
                                        </div>
                                    </div>
                                </div>';
                        }
                    } else {
                        echo '<div class="alert alert-info">لم تقم بالإجابة على أي أسئلة بعد.</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger">لا يمكن تحميل الإجابات.</div>';
                }
                ?>
            </div>
            
            <div class="tab-pane fade" id="saved" role="tabpanel" aria-labelledby="saved-tab">
                <div class="alert alert-info">لا توجد أسئلة محفوظة.</div>
            </div>
            
            <div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="settings-tab">
                <form>
                    <div class="mb-3">
                        <label for="update_username" class="form-label">اسم المستخدم</label>
                        <input type="text" class="form-control" id="update_username" value="<?php echo htmlspecialchars($user['username']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="update_email" class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control" id="update_email" value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="update_password" class="form-label">كلمة المرور الجديدة</label>
                        <input type="password" class="form-control" id="update_password">
                    </div>
                    <div class="mb-3">
                        <label for="confirm_update_password" class="form-label">تأكيد كلمة المرور</label>
                        <input type="password" class="form-control" id="confirm_update_password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">اللغة المفضلة</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="language" id="language_ar" value="ar" <?php echo isset($_SESSION['language']) && $_SESSION['language'] == 'ar' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="language_ar">العربية</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="language" id="language_en" value="en" <?php echo isset($_SESSION['language']) && $_SESSION['language'] == 'en' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="language_en">English</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                </form>
            </div>
        </div>
    </div>
</div>
