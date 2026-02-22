<?php
header("Content-Type:text/plain");
echo password_hash("password123", PASSWORD_BCRYPT);
