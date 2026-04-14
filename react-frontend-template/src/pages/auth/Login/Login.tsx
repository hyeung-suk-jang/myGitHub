import { useState, type FormEvent } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import styles from './Login.module.scss';
import { Input }  from '@/components/common/Input';
import { Button } from '@/components/common/Button';
import { useAppDispatch, useAppSelector } from '@/store';
import { loginThunk } from '@/store/modules/auth.store';
import { ROUTE } from '@/constants/route';
import { isEmail, isRequired } from '@/utils/validation/validator';

export function Login() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const isLoading = useAppSelector((s) => s.auth.status === 'loading');

  const [email,    setEmail]    = useState('');
  const [password, setPassword] = useState('');
  const [errors,   setErrors]   = useState<{ email?: string; password?: string }>({});

  const validate = () => {
    const newErrors: typeof errors = {};
    newErrors.email    = isEmail(email) ?? undefined;
    newErrors.password = isRequired(password) ?? undefined;

    if (!email) newErrors.email = isRequired(email) ?? undefined;

    setErrors(newErrors);
    return Object.values(newErrors).every((v) => !v);
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    if (!validate()) return;

    const result = await dispatch(loginThunk({ email, password }));
    if (loginThunk.fulfilled.match(result)) {
      navigate(ROUTE.DASHBOARD);
    }
  };

  return (
    <div className={styles.container}>
      <h1 className={styles.title}>로그인</h1>
      <p className={styles.subtitle}>계정에 로그인하세요</p>

      <form onSubmit={handleSubmit} className={styles.form} noValidate>
        <Input
          type="email"
          label="이메일"
          placeholder="이메일을 입력하세요"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          error={errors.email}
          isRequired
          autoComplete="email"
        />
        <Input
          type="password"
          label="비밀번호"
          placeholder="비밀번호를 입력하세요"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          error={errors.password}
          isRequired
          autoComplete="current-password"
        />
        <div className={styles.forgotLink}>
          <Link to={ROUTE.FORGOT_PASSWORD}>비밀번호를 잊으셨나요?</Link>
        </div>
        <Button type="submit" fullWidth isLoading={isLoading}>
          로그인
        </Button>
      </form>

      <p className={styles.signupLink}>
        계정이 없으신가요? <Link to={ROUTE.SIGNUP}>회원가입</Link>
      </p>
    </div>
  );
}
