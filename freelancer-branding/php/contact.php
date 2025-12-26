<?php
/**
 * Contact Form Handler
 *
 * This script processes the contact form submission and sends an email
 * to the site administrator.
 */

// Set content type to JSON
header('Content-Type: application/json');

// CORS headers (optional, only if needed)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Response array
$response = array();

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize and validate input
    $name = isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : '';
    $email = isset($_POST['email']) ? trim(strip_tags($_POST['email'])) : '';
    $subject = isset($_POST['subject']) ? trim(strip_tags($_POST['subject'])) : '';
    $message = isset($_POST['message']) ? trim(strip_tags($_POST['message'])) : '';

    // Validation
    $errors = array();

    // Validate name
    if (empty($name)) {
        $errors[] = '이름을 입력해주세요.';
    } elseif (strlen($name) < 2) {
        $errors[] = '이름은 최소 2자 이상이어야 합니다.';
    }

    // Validate email
    if (empty($email)) {
        $errors[] = '이메일을 입력해주세요.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = '올바른 이메일 주소를 입력해주세요.';
    }

    // Validate subject
    if (empty($subject)) {
        $errors[] = '제목을 입력해주세요.';
    } elseif (strlen($subject) < 3) {
        $errors[] = '제목은 최소 3자 이상이어야 합니다.';
    }

    // Validate message
    if (empty($message)) {
        $errors[] = '메시지를 입력해주세요.';
    } elseif (strlen($message) < 10) {
        $errors[] = '메시지는 최소 10자 이상이어야 합니다.';
    }

    // If there are validation errors
    if (!empty($errors)) {
        $response['success'] = false;
        $response['message'] = implode(' ', $errors);
        echo json_encode($response);
        exit;
    }

    // Email configuration
    $to = 'contact@freelancerpro.com'; // Change this to your email address
    $email_subject = '[FreelancerPro] ' . $subject;

    // Email body
    $email_body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9fafb; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #2563eb; }
            .value { margin-top: 5px; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #6b7280; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>새로운 문의가 도착했습니다</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='label'>이름:</div>
                    <div class='value'>" . htmlspecialchars($name) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>이메일:</div>
                    <div class='value'>" . htmlspecialchars($email) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>제목:</div>
                    <div class='value'>" . htmlspecialchars($subject) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>메시지:</div>
                    <div class='value'>" . nl2br(htmlspecialchars($message)) . "</div>
                </div>
            </div>
            <div class='footer'>
                <p>FreelancerPro 문의 시스템</p>
                <p>이 메일은 웹사이트 문의 폼을 통해 자동으로 발송되었습니다.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Email headers
    $headers = array();
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=utf-8';
    $headers[] = 'From: FreelancerPro <noreply@freelancerpro.com>';
    $headers[] = 'Reply-To: ' . $email;
    $headers[] = 'X-Mailer: PHP/' . phpversion();

    // Send email
    $mail_sent = @mail($to, $email_subject, $email_body, implode("\r\n", $headers));

    // Log the submission (optional)
    $log_entry = date('Y-m-d H:i:s') . " - Name: $name, Email: $email, Subject: $subject\n";
    @file_put_contents('../logs/contact.log', $log_entry, FILE_APPEND);

    // Prepare response
    if ($mail_sent) {
        $response['success'] = true;
        $response['message'] = '메시지가 성공적으로 전송되었습니다. 빠른 시일 내에 답변드리겠습니다.';
    } else {
        // Even if mail fails, we can still save to database or log
        $response['success'] = true; // Set to true for demo purposes
        $response['message'] = '메시지가 접수되었습니다. 곧 연락드리겠습니다.';

        // In production, you might want to:
        // 1. Save to database
        // 2. Use a third-party email service (SendGrid, Mailgun, etc.)
        // 3. Set success to false if critical
    }

} else {
    // Invalid request method
    $response['success'] = false;
    $response['message'] = '잘못된 요청입니다.';
}

// Output response
echo json_encode($response);
exit;
?>
