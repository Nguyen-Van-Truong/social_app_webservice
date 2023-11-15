<?php

class PasswordManager {
    /**
     * Mã hóa mật khẩu.
     *
     * @param string $password Mật khẩu cần được mã hóa.
     * @return string Mật khẩu đã được mã hóa.
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Xác thực mật khẩu so với mật khẩu đã mã hóa.
     *
     * @param string $inputPassword Mật khẩu đầu vào cần được xác thực.
     * @param string $hashedPassword Mật khẩu đã được mã hóa.
     * @return bool Trả về true nếu mật khẩu đầu vào khớp với mật khẩu đã mã hóa.
     */
    public static function verifyPassword($inputPassword, $hashedPassword) {
        return password_verify($inputPassword, $hashedPassword);
    }
}
?>
