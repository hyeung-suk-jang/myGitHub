// 사용자 타입
export interface User {
  id: string;
  username: string;
  email: string;
  phone: string;
  password: string;
  isAdmin: boolean;
  createdAt: Date;
}

// 좌석 타입
export interface Seat {
  id: string;
  number: number;
  type: 'single' | 'double' | 'group';
  status: 'available' | 'occupied' | 'reserved';
  floor: number;
}

// 이용권 타입
export interface Ticket {
  id: string;
  name: string;
  type: 'hourly' | 'daily' | 'weekly' | 'monthly';
  duration: number; // 분 단위
  price: number;
  description: string;
}

// 결제 타입
export interface Payment {
  id: string;
  userId: string;
  ticketId: string;
  amount: number;
  method: 'card' | 'cash' | 'transfer';
  status: 'pending' | 'completed' | 'failed';
  createdAt: Date;
}

// 이용 세션 타입
export interface Session {
  id: string;
  userId: string;
  seatId: string;
  ticketId: string;
  startTime: Date;
  endTime: Date | null;
  remainingMinutes: number;
  status: 'active' | 'completed' | 'expired';
}

// 이용 내역 타입
export interface UsageHistory {
  id: string;
  userId: string;
  seatNumber: number;
  ticketName: string;
  startTime: Date;
  endTime: Date;
  duration: number; // 분 단위
  amount: number;
}
