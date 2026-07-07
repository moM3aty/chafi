<?php
// مسار الملف: ajax/logout.php
// الوظيفة: تسجيل الخروج

session_start();
session_destroy();
header("Location: ../index.php");
exit;
?>