import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import type { Seat, Ticket, Session, UsageHistory } from '../types';

interface StudyCafeState {
  seats: Seat[];
  tickets: Ticket[];
  sessions: Session[];
  usageHistory: UsageHistory[];
  selectedSeat: Seat | null;
  selectedTicket: Ticket | null;

  // Seat actions
  selectSeat: (seat: Seat) => void;
  updateSeatStatus: (seatId: string, status: Seat['status']) => void;

  // Ticket actions
  selectTicket: (ticket: Ticket) => void;

  // Session actions
  startSession: (userId: string, seatId: string, ticketId: string) => void;
  endSession: (sessionId: string) => void;
  getActiveSession: (userId: string) => Session | undefined;

  // Usage history
  addUsageHistory: (history: UsageHistory) => void;
  getUserHistory: (userId: string) => UsageHistory[];
}

// Mock data
const initialSeats: Seat[] = Array.from({ length: 30 }, (_, i) => ({
  id: `seat-${i + 1}`,
  number: i + 1,
  type: i < 20 ? 'single' : i < 28 ? 'double' : 'group',
  status: 'available',
  floor: Math.floor(i / 15) + 1,
}));

const initialTickets: Ticket[] = [
  {
    id: 'ticket-1',
    name: '2시간 이용권',
    type: 'hourly',
    duration: 120,
    price: 4000,
    description: '2시간 자유롭게 이용 가능',
  },
  {
    id: 'ticket-2',
    name: '4시간 이용권',
    type: 'hourly',
    duration: 240,
    price: 7000,
    description: '4시간 자유롭게 이용 가능',
  },
  {
    id: 'ticket-3',
    name: '종일 이용권',
    type: 'daily',
    duration: 720,
    price: 15000,
    description: '12시간 자유롭게 이용 가능',
  },
  {
    id: 'ticket-4',
    name: '주간 이용권',
    type: 'weekly',
    duration: 10080,
    price: 80000,
    description: '1주일 무제한 이용 가능',
  },
  {
    id: 'ticket-5',
    name: '월간 이용권',
    type: 'monthly',
    duration: 43200,
    price: 250000,
    description: '1개월 무제한 이용 가능',
  },
];

export const useStudyCafeStore = create<StudyCafeState>()(
  persist(
    (set, get) => ({
      seats: initialSeats,
      tickets: initialTickets,
      sessions: [],
      usageHistory: [],
      selectedSeat: null,
      selectedTicket: null,

      selectSeat: (seat) => {
        set({ selectedSeat: seat });
      },

      updateSeatStatus: (seatId, status) => {
        set((state) => ({
          seats: state.seats.map((seat) =>
            seat.id === seatId ? { ...seat, status } : seat
          ),
        }));
      },

      selectTicket: (ticket) => {
        set({ selectedTicket: ticket });
      },

      startSession: (userId, seatId, ticketId) => {
        const ticket = get().tickets.find((t) => t.id === ticketId);
        if (!ticket) return;

        const newSession: Session = {
          id: `session-${Date.now()}`,
          userId,
          seatId,
          ticketId,
          startTime: new Date(),
          endTime: null,
          remainingMinutes: ticket.duration,
          status: 'active',
        };

        set((state) => ({
          sessions: [...state.sessions, newSession],
        }));

        get().updateSeatStatus(seatId, 'occupied');
      },

      endSession: (sessionId) => {
        const session = get().sessions.find((s) => s.id === sessionId);
        if (!session) return;

        const endTime = new Date();
        const duration = Math.floor(
          (endTime.getTime() - session.startTime.getTime()) / (1000 * 60)
        );

        set((state) => ({
          sessions: state.sessions.map((s) =>
            s.id === sessionId
              ? { ...s, endTime, status: 'completed' as const }
              : s
          ),
        }));

        get().updateSeatStatus(session.seatId, 'available');

        // Add to usage history
        const seat = get().seats.find((s) => s.id === session.seatId);
        const ticket = get().tickets.find((t) => t.id === session.ticketId);

        if (seat && ticket) {
          const history: UsageHistory = {
            id: `history-${Date.now()}`,
            userId: session.userId,
            seatNumber: seat.number,
            ticketName: ticket.name,
            startTime: session.startTime,
            endTime,
            duration,
            amount: ticket.price,
          };
          get().addUsageHistory(history);
        }
      },

      getActiveSession: (userId) => {
        return get().sessions.find(
          (s) => s.userId === userId && s.status === 'active'
        );
      },

      addUsageHistory: (history) => {
        set((state) => ({
          usageHistory: [...state.usageHistory, history],
        }));
      },

      getUserHistory: (userId) => {
        return get().usageHistory.filter((h) => h.userId === userId);
      },
    }),
    {
      name: 'study-cafe-storage',
    }
  )
);
