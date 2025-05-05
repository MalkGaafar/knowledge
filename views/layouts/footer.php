<!DOCTYPE html>
<html lang="<?php echo isset($_SESSION['language']) ? $_SESSION['language'] : 'ar'; ?>" dir="<?php echo isset($_SESSION['language']) && $_SESSION['language'] == 'en' ? 'ltr' : 'rtl'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منصة المعرفة العربية - المساحة المثالية للأسئلة والإجابات</title>
    
    <?php if (isset($_SESSION['language']) && $_SESSION['language'] == 'en'): ?>
        <!-- Bootstrap LTR CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <?php else: ?>
        <!-- Bootstrap RTL CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <?php endif; ?>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <!-- Language-specific CSS -->
    <?php if (isset($_SESSION['language']) && $_SESSION['language'] == 'en'): ?>
        <style>
            body {
                text-align: left;
                direction: ltr;
            }
        </style>
    <?php endif; ?>
</head>
<body>
</div>
    
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>منصة المعرفة العربية</h5>
                    <p>منصة لتبادل المعرفة بين مطوري البرمجيات وعلماء البيانات والذكاء الاصطناعي باللغة العربية</p>
                </div>
                <div class="col-md-4">
                    <h5>روابط مهمة</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">سياسة الخصوصية</a></li>
                        <li><a href="#" class="text-white">شروط الاستخدام</a></li>
                        <li><a href="#" class="text-white">تواصل معنا</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>تابعنا على</h5>
                    <div class="social-icons">
                        <a href="#" class="text-white mx-1"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white mx-1"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white mx-1"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-white mx-1"><i class="fab fa-github"></i></a>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <p class="mb-0">© 2025 منصة المعرفة العربية. جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Main JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>

