<?php
$senha = 'admin123';
$hash = password_hash($senha, PASSWORD_DEFAULT);
echo "Hash da senha 'admin123': " . $hash; 