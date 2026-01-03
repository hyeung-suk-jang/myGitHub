/**
 * API 서비스
 * 백엔드 API와 통신
 */

import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// API Base URL (환경에 따라 변경)
const API_BASE_URL = __DEV__
  ? 'http://localhost/equipment-rental-platform/api'
  : 'https://your-production-domain.com/api';

// Axios 인스턴스 생성
const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
  },
});

// 요청 인터셉터 (토큰 추가)
api.interceptors.request.use(
  async config => {
    const token = await AsyncStorage.getItem('userToken');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  error => {
    return Promise.reject(error);
  },
);

// 응답 인터셉터 (에러 처리)
api.interceptors.response.use(
  response => response.data,
  async error => {
    if (error.response?.status === 401) {
      // 인증 만료 - 로그아웃 처리
      await AsyncStorage.removeItem('userToken');
      // 로그인 화면으로 리다이렉트 (navigation 필요)
    }
    return Promise.reject(error);
  },
);

export default {
  // 인증
  login: (email, password) =>
    api.post('/auth.php?action=login', {email, password}),

  register: userData =>
    api.post('/auth.php?action=register', userData),

  logout: () =>
    api.get('/auth.php?action=logout'),

  getProfile: () =>
    api.get('/auth.php?action=profile'),

  // 장비
  getEquipmentList: params =>
    api.get('/equipment.php?action=list', {params}),

  getEquipmentDetail: id =>
    api.get('/equipment.php?action=detail', {params: {id}}),

  createEquipment: data =>
    api.post('/equipment.php?action=create', data),

  getMyEquipment: () =>
    api.get('/equipment.php?action=my-equipment'),

  // 대여
  createRental: data =>
    api.post('/rental.php?action=create', data),

  getMyRentals: type =>
    api.get('/rental.php?action=my-rentals', {params: {type}}),

  approveRental: rentalId =>
    api.put('/rental.php?action=approve', {rental_id: rentalId}),

  rejectRental: (rentalId, reason) =>
    api.put('/rental.php?action=reject', {rental_id: rentalId, reason}),

  // 채팅
  getChatRooms: () =>
    api.get('/chat.php?action=get-rooms'),

  getMessages: roomId =>
    api.get('/chat.php?action=get-messages', {params: {room_id: roomId}}),

  sendMessage: (roomId, message) =>
    api.post('/chat.php?action=send-message', {room_id: roomId, message}),

  // 결제
  preparePayment: rentalId =>
    api.post('/toss_payment.php?action=prepare', {rental_id: rentalId}),

  // 추천
  getRecommendations: () =>
    api.get('/recommendation.php?action=get-recommendations'),

  getTrending: () =>
    api.get('/recommendation.php?action=trending'),

  // 보험
  getInsuranceQuote: rentalId =>
    api.post('/insurance.php?action=quote', {rental_id: rentalId}),

  purchaseInsurance: data =>
    api.post('/insurance.php?action=purchase', data),
};
