<?php
// Include traditional session service
include __DIR__ . '/../services/session.php';

// Include auth middleware for CMU OAuth
include_once __DIR__ . '/../auth/middleware.php';

// Check authentication on every admin page
requireAuthentication();
?>