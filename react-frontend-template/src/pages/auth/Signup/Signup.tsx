import { useState, type FormEvent } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import styles from './Signup.module.scss';
import { Input }  from '@/components/common/Input';
import { Button } from '@/components/common/Button';
import { ROUTE } from '@/constants/route';
import { signup } from '@/services/modules/auth.api';
import { isEmail, isPassword, isRequired } from '@/utils/validation/validator';

export function Signup() {
  const navigate  = useNavigate();
  const [isLoading, setIsLoading] = useState(false);
  const [form, setForm] = useState({ name: '', email: '', password: '', passwordConfirm: '' });
  const [errors, setErrors] = useState<Partial<typeof form>>({});

  const handleChange = (field: keyof typeof form) => (e: React.ChangeEvent<HTMLInputElement>) => {
    setForm((prev) => ({ ...prev, [field]: e.target.value }));
  };

  const validate = () => {
    const newErrors: Partial<typeof form> = {};
    if (!form.name)     newErrors.name = isRequired(form.name) ?? undefined;
    if (!form.email)    newErrors.email = isRequired(form.email) ?? undefined;
    else                newErrors.email = isEmail(form.email) ?? undefined;
    if (!form.password) newErrors.password = isRequired(form.password) ?? undefined;
    else                newErrors.password = isPassword(form.password) ?? undefined;
    if (form.password !== form.passwordConfirm) {
      newErrors.passwordConfirm = '비밀번호가 일치하지 않습니다.';
    }
    setErrors(newErrors);
    return Object.values(newErrors).every((v) => !v);
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    if (!validate()) return;
    setIsLoading(true);
    try {
      await signup(form);
      navigate(ROUTE.LOGIN);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className={styles.container}>
      <h1 className={styles.title}>회원가입</h1>
      <form onSubmit={handleSubmit} className={styles.form} noValidate>
        <Input label="이름" placeholder="이름을 입력하세요" value={form.name}
          onChange={handleChange('name')} error={errors.name} isRequired />
        <Input type="email" label="이메일" placeholder="이메일을 입력하세요" value={form.email}
          onChange={handleChange('email')} error={errors.email} isRequired />
        <Input type="password" label="비밀번호" placeholder="비밀번호를 입력하세요" value={form.password}
          onChange={handleChange('password')} error={errors.password} isRequired />
        <Input type="password" label="비밀번호 확인" placeholder="비밀번호를 다시 입력하세요"
          value={form.passwordConfirm} onChange={handleChange('passwordConfirm')}
          error={errors.passwordConfirm} isRequired />
        <Button type="submit" fullWidth isLoading={isLoading}>회원가입</Button>
      </form>
      <p className={styles.loginLink}>
        이미 계정이 있으신가요? <Link to={ROUTE.LOGIN}>로그인</Link>
      </p>
    </div>
  );
}
