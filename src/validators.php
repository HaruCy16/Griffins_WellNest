<?php
/**
 * Input Validators
 * Wellnest Mental Health Web Application
 */

// =============================================================================
// VALIDATION RESULT CLASS
// =============================================================================

class ValidationResult {
    public bool $isValid = true;
    public array $errors = [];
    
    public function addError(string $field, string $message): void {
        $this->isValid = false;
        $this->errors[$field] = $message;
    }
    
    public function hasError(string $field): bool {
        return isset($this->errors[$field]);
    }
    
    public function getError(string $field): string {
        return $this->errors[$field] ?? '';
    }
    
    public function getAllErrors(): array {
        return $this->errors;
    }
    
    public function getFirstError(): string {
        return reset($this->errors) ?: '';
    }
}

// =============================================================================
// EMAIL VALIDATION
// =============================================================================

/**
 * Validate email format
 * @param string $email Email to validate
 * @return bool
 */
function isValidEmail(string $email): bool {
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        return false;
    }
    // Only allow school email domain
    $allowedDomain = '@my.nst.edu.ph';
    return str_ends_with(strtolower($email), $allowedDomain);
}

/**
 * Check if email exists in database
 * @param string $email Email to check
 * @param int|null $excludeUserId User ID to exclude (for updates)
 * @return bool
 */
function emailExists(string $email, ?int $excludeUserId = null): bool {
    $sql = "SELECT user_id FROM users WHERE email = ? AND deleted_at IS NULL";
    $params = [$email];
    
    if ($excludeUserId) {
        $sql .= " AND user_id != ?";
        $params[] = $excludeUserId;
    }
    
    $result = Database::fetchOne($sql, $params);
    return $result !== false;
}

// =============================================================================
// PASSWORD VALIDATION
// =============================================================================

/**
 * Validate password strength
 * @param string $password Password to validate
 * @return ValidationResult
 */
function validatePassword(string $password): ValidationResult {
    $result = new ValidationResult();
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $result->addError('password', 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.');
        return $result;
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $result->addError('password', 'Password must contain at least one uppercase letter.');
        return $result;
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $result->addError('password', 'Password must contain at least one lowercase letter.');
        return $result;
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $result->addError('password', 'Password must contain at least one number.');
        return $result;
    }
    
    return $result;
}

/**
 * Hash password using bcrypt
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
}

/**
 * Verify password against hash
 * @param string $password Plain text password
 * @param string $hash Stored hash
 * @return bool
 */
function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

// =============================================================================
// NAME VALIDATION
// =============================================================================

/**
 * Validate name (first or last)
 * @param string $name Name to validate
 * @param string $fieldName Field name for error message
 * @return ValidationResult
 */
function validateName(string $name, string $fieldName = 'Name'): ValidationResult {
    $result = new ValidationResult();
    
    if (empty(trim($name))) {
        $result->addError(strtolower($fieldName), "{$fieldName} is required.");
        return $result;
    }
    
    if (strlen($name) < 2) {
        $result->addError(strtolower($fieldName), "{$fieldName} must be at least 2 characters.");
        return $result;
    }
    
    if (strlen($name) > 100) {
        $result->addError(strtolower($fieldName), "{$fieldName} must not exceed 100 characters.");
        return $result;
    }
    
    if (!preg_match('/^[a-zA-Z\s\-\'\.]+$/', $name)) {
        $result->addError(strtolower($fieldName), "{$fieldName} contains invalid characters.");
        return $result;
    }
    
    return $result;
}

// =============================================================================
// STUDENT ID VALIDATION
// =============================================================================

/**
 * Validate student ID format: YYYYSSSNNNN (e.g., 2025SHS0299)
 * - YYYY = 4-digit year
 * - SSS = 3-letter school type (e.g., SHS, JHS)
 * - NNNN = 4-digit student number
 * 
 * @param string $studentId Student ID to validate
 * @return ValidationResult
 */
function validateStudentId(string $studentId): ValidationResult {
    $result = new ValidationResult();
    
    if (empty(trim($studentId))) {
        $result->addError('student_id', 'Student ID is required.');
        return $result;
    }
    
    // Enforce format: 4 digits + 3 letters + 4 digits (e.g., 2025SHS0299)
    if (!preg_match('/^\d{4}[A-Z]{3}\d{4}$/', strtoupper($studentId))) {
        $result->addError('student_id', 'Student ID must be in format YYYYSSSNNNN (e.g., 2025SHS0299).');
        return $result;
    }
    
    return $result;
}

/**
 * Check if student ID exists
 * @param string $studentId Student ID to check
 * @param int|null $excludeUserId User ID to exclude
 * @return bool
 */
function studentIdExists(string $studentId, ?int $excludeUserId = null): bool {
    $sql = "SELECT user_id FROM users WHERE student_id = ? AND deleted_at IS NULL";
    $params = [$studentId];
    
    if ($excludeUserId) {
        $sql .= " AND user_id != ?";
        $params[] = $excludeUserId;
    }
    
    $result = Database::fetchOne($sql, $params);
    return $result !== false;
}

// =============================================================================
// REGISTRATION VALIDATION
// =============================================================================

/**
 * Validate registration form data
 * @param array $data Form data
 * @return ValidationResult
 */
function validateRegistration(array $data): ValidationResult {
    $result = new ValidationResult();
    
    // Validate first name
    $firstNameResult = validateName($data['first_name'] ?? '', 'First name');
    if (!$firstNameResult->isValid) {
        $result->addError('first_name', $firstNameResult->getFirstError());
    }
    
    // Validate last name
    $lastNameResult = validateName($data['last_name'] ?? '', 'Last name');
    if (!$lastNameResult->isValid) {
        $result->addError('last_name', $lastNameResult->getFirstError());
    }
    
    // Validate email
    $email = $data['email'] ?? '';
    if (empty($email)) {
        $result->addError('email', 'Email is required.');
    } elseif (!isValidEmail($email)) {
        $result->addError('email', 'Please use your school email (@my.nst.edu.ph).');
    } elseif (emailExists($email)) {
        $result->addError('email', 'This email is already registered.');
    }
    
    // Validate student ID (for students only)
    if (($data['role_id'] ?? ROLE_STUDENT) == ROLE_STUDENT) {
        $studentId = $data['student_id'] ?? '';
        if (empty($studentId)) {
            $result->addError('student_id', 'Student ID is required.');
        } else {
            $studentIdResult = validateStudentId($studentId);
            if (!$studentIdResult->isValid) {
                $result->addError('student_id', $studentIdResult->getFirstError());
            } elseif (studentIdExists($studentId)) {
                $result->addError('student_id', 'This Student ID is already registered.');
            }
        }
    }
    
    // Validate password
    $password = $data['password'] ?? '';
    if (empty($password)) {
        $result->addError('password', 'Password is required.');
    } else {
        $passwordResult = validatePassword($password);
        if (!$passwordResult->isValid) {
            $result->addError('password', $passwordResult->getFirstError());
        }
    }
    
    // Validate password confirmation
    $confirmPassword = $data['confirm_password'] ?? '';
    if ($password !== $confirmPassword) {
        $result->addError('confirm_password', 'Passwords do not match.');
    }
    
    return $result;
}

// =============================================================================
// LOGIN VALIDATION
// =============================================================================

/**
 * Validate login form data
 * @param array $data Form data
 * @return ValidationResult
 */
function validateLogin(array $data): ValidationResult {
    $result = new ValidationResult();
    
    $email = $data['email'] ?? '';
    if (empty($email)) {
        $result->addError('email', 'Email is required.');
    } elseif (!isValidEmail($email)) {
        $result->addError('email', 'Please use your school email (@my.nst.edu.ph).');
    }
    
    $password = $data['password'] ?? '';
    if (empty($password)) {
        $result->addError('password', 'Password is required.');
    }
    
    return $result;
}
