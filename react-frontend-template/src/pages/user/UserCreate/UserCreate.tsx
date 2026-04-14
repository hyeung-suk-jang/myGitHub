import { useState, type FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import styles from './UserCreate.module.scss';
import { Input }  from '@/components/common/Input';
import { Button } from '@/components/common/Button';
import { Select } from '@/components/form/Select';
import { useAppDispatch } from '@/store';
import { createUserThunk } from '@/store/modules/user.store';
import { ROUTE } from '@/constants/route';
import { isEmail, isPassword, isRequired } from '@/utils/validation/validator';
import type { UserRole } from '@/services/types/user.type';

const roleOptions = [
  { label: '일반 사용자', value: 'user' },
  { label: '매니저',      value: 'manager' },
  { label: '관리자',      value: 'admin' },
];

export function UserCreate() {
  const dispatch   = useAppDispatch();
  const navigate   = useNavigate();
  const [isLoading, setIsLoading] = useState(false);
  const [form, setForm] = useState({ name: '', email: '', password: '', role: 'user' as UserRole });
  const [errors, setErrors] = useState<Partial<Record<keyof typeof form, string>>>({});

  const handleChange = (field: keyof typeof form) => (
    e: React.ChangeEvent<HTMLInputElement | HTMLSelectElement>,
  ) => setForm((prev) => ({ ...prev, [field]: e.target.value }));

  const validate = () => {
    const newErrors: typeof errors = {};
    if (!form.name)     newErrors.name     = isRequired(form.name)         ?? undefined;
    if (!form.email)    newErrors.email    = isRequired(form.email)        ?? undefined;
    else                newErrors.email    = isEmail(form.email)           ?? undefined;
    if (!form.password) newErrors.password = isRequired(form.password)     ?? undefined;
    else                newErrors.password = isPassword(form.password)     ?? undefined;
    setErrors(newErrors);
    return Object.values(newErrors).every((v) => !v);
  };

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault();
    if (!validate()) return;
    setIsLoading(true);
    try {
      await dispatch(createUserThunk(form));
      navigate(ROUTE.USER_LIST);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className={styles.container}>
      <div className={styles.header}>
        <h1 className={styles.title}>새 사용자 등록</h1>
        <Button variant="outline" onClick={() => navigate(ROUTE.USER_LIST)}>취소</Button>
      </div>

      <form onSubmit={handleSubmit} className={styles.form} noValidate>
        <Input label="이름" placeholder="이름을 입력하세요" value={form.name}
          onChange={handleChange('name')} error={errors.name} isRequired />
        <Input type="email" label="이메일" placeholder="이메일을 입력하세요" value={form.email}
          onChange={handleChange('email')} error={errors.email} isRequired />
        <Input type="password" label="비밀번호" placeholder="비밀번호를 입력하세요" value={form.password}
          onChange={handleChange('password')} error={errors.password} isRequired />
        <Select label="역할" options={roleOptions} value={form.role}
          onChange={handleChange('role')} />
        <div className={styles.actions}>
          <Button type="submit" isLoading={isLoading}>등록</Button>
        </div>
      </form>
    </div>
  );
}
