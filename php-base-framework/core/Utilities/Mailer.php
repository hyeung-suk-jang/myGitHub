<?php

namespace Core\Utilities;

class Mailer
{
    private $config;
    private $from;
    private $to;
    private $subject;
    private $body;
    private $headers = [];

    public function __construct($config = [])
    {
        $this->config = $config;
        $this->from = $config['from'] ?? 'noreply@example.com';
    }

    public function to($email, $name = '')
    {
        $this->to = $name ? "{$name} <{$email}>" : $email;
        return $this;
    }

    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function body($body, $isHtml = true)
    {
        $this->body = $body;

        if ($isHtml) {
            $this->headers[] = "MIME-Version: 1.0";
            $this->headers[] = "Content-type: text/html; charset=UTF-8";
        }

        return $this;
    }

    public function send()
    {
        $this->headers[] = "From: {$this->from}";
        $this->headers[] = "Reply-To: {$this->from}";
        $this->headers[] = "X-Mailer: PHP/" . phpversion();

        $headers = implode("\r\n", $this->headers);

        if (isset($this->config['smtp']) && $this->config['smtp']) {
            return $this->sendViaSMTP();
        }

        return mail($this->to, $this->subject, $this->body, $headers);
    }

    private function sendViaSMTP()
    {
        // SMTP implementation would go here
        // This is a placeholder for SMTP functionality
        return true;
    }

    public function sendVerificationEmail($email, $token)
    {
        $verificationLink = $this->config['app_url'] . "/verify-email?token={$token}";

        $body = "
            <html>
            <body>
                <h2>이메일 인증</h2>
                <p>아래 링크를 클릭하여 이메일을 인증해주세요:</p>
                <p><a href='{$verificationLink}'>이메일 인증하기</a></p>
                <p>링크가 작동하지 않으면 다음 URL을 복사하여 브라우저에 붙여넣으세요:</p>
                <p>{$verificationLink}</p>
            </body>
            </html>
        ";

        return $this->to($email)
            ->subject('이메일 인증')
            ->body($body)
            ->send();
    }

    public function sendPasswordReset($email, $token)
    {
        $resetLink = $this->config['app_url'] . "/reset-password?token={$token}";

        $body = "
            <html>
            <body>
                <h2>비밀번호 재설정</h2>
                <p>아래 링크를 클릭하여 비밀번호를 재설정하세요:</p>
                <p><a href='{$resetLink}'>비밀번호 재설정</a></p>
                <p>링크가 작동하지 않으면 다음 URL을 복사하여 브라우저에 붙여넣으세요:</p>
                <p>{$resetLink}</p>
            </body>
            </html>
        ";

        return $this->to($email)
            ->subject('비밀번호 재설정')
            ->body($body)
            ->send();
    }
}
