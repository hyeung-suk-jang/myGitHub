import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import HomePage from './pages/HomePage';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import SeatSelectPage from './pages/SeatSelectPage';
import TicketSelectPage from './pages/TicketSelectPage';
import PaymentPage from './pages/PaymentPage';
import MyPage from './pages/MyPage';
import AdminPage from './pages/AdminPage';

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<HomePage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route path="/seats" element={<SeatSelectPage />} />
        <Route path="/tickets" element={<TicketSelectPage />} />
        <Route path="/payment" element={<PaymentPage />} />
        <Route path="/my-page" element={<MyPage />} />
        <Route path="/admin" element={<AdminPage />} />
      </Routes>
    </Router>
  );
}

export default App;
