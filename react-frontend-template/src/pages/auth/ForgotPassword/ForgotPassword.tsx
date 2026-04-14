import { useState, type FormEvent } from 'react';
import { Link } from 'react-router-dom';
import styles from './ForgotPassword.module.scss';
import { Input }  from '@/components/common/Input';
import { Button } from '@/components/common/Button';
import { ROUTE } from '@/constants/route';
import { forgotPassword } from '@/services/modules/auth.api';
import { isEmail, isRequired } from '@/utils/validation/validator';

export function ForgotPassword() {
  const [email,      setEmail]      = useState('');
  const [error,      setError]      = useState<string | undefined>();
  const [isLoading,  setIsLoading]  = useState(false);
  const [isSent,     setIsSent]     = useState(false);

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    const err = isRequired(email) ?? isEmail(email);
    if (err) { setError(err); return; }

    setIsLoading(true);
    try {
      await forgotPassword(email);
      setIsSent(true);
    } finally {
      setIsLoading(false);
    }
  };

  if (isSent) {
    return (
      <div className={styles.successContainer}>
        <p className={styles.successMessage}>
          {email} 주소로 비밀번호 재설정 링크를 발송했습니다.
        </p>
        <Link to={ROUTE.LOGIN}>
          <Button variant="outline" fullWidth>로그인으로 돌아가기</Button>
        </Link>
      </div>
    );
  }

  return (
    <div className={styles.container}>
      <h1 className={styles.title}>비밀번호 찾기</h1>
      <p className={styles.description}>
        가입한 이메일 주소를 입력하시면 비밀번호 재설정 링크를 보내드립니다.
      </p>
      <form onSubmit={handleSubmit} className={styles.form} noValidate>
        <Input type="email" label="이메일" placeholder="가입한 이메일을 입력하세요"
          value={email} onChange={(e) => { setEmail(e.target.value); setError(undefined); }}
          error={error} isRequired />
        <Button type="submit" fullWidth isLoading={isLoading}>재설정 링크 발송</Button>
      </form>
      <p className={styles.backLink}><Link to={ROUTE.LOGIN}>로그인으로 돌아가기</Link></p>
    </div>
  );
}
