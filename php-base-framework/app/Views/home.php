<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WenIT - 혁신적인 IT 솔루션 파트너</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/layouts/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="container">
                <h1 class="hero-title fade-in-up">디지털 혁신을 선도하는<br>IT 솔루션 파트너</h1>
                <p class="hero-subtitle fade-in-up delay-1">최첨단 기술과 창의적 사고로 비즈니스의 미래를 설계합니다</p>
                <div class="hero-buttons fade-in-up delay-2">
                    <a href="#services" class="btn btn-primary btn-large">서비스 알아보기</a>
                    <a href="#contact" class="btn btn-outline btn-large">상담 신청</a>
                </div>
            </div>
        </div>
        <div class="scroll-indicator">
            <span></span>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section section-padding">
        <div class="container">
            <div class="section-header text-center">
                <span class="section-label">ABOUT US</span>
                <h2 class="section-title">혁신적인 기술로 미래를 창조합니다</h2>
                <p class="section-description">고객의 성공이 곧 우리의 성공입니다. 최고의 기술력과 노하우로 최상의 솔루션을 제공합니다.</p>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3 class="stat-number" data-count="350">0</h3>
                    <p class="stat-label">성공 프로젝트</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="stat-number" data-count="500">0</h3>
                    <p class="stat-label">만족한 고객</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3 class="stat-number" data-count="25">0</h3>
                    <p class="stat-label">수상 경력</p>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="stat-number" data-count="15">0</h3>
                    <p class="stat-label">경력 연수</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section section-padding bg-light">
        <div class="container">
            <div class="section-header text-center">
                <span class="section-label">SERVICES</span>
                <h2 class="section-title">전문적인 IT 서비스</h2>
                <p class="section-description">다양한 분야의 전문성을 바탕으로 최적의 솔루션을 제공합니다</p>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <h3>웹 개발</h3>
                    <p>반응형 웹사이트부터 복잡한 웹 애플리케이션까지 최신 기술로 개발합니다.</p>
                    <ul class="service-features">
                        <li>프론트엔드 개발</li>
                        <li>백엔드 시스템</li>
                        <li>데이터베이스 설계</li>
                    </ul>
                    <a href="#" class="service-link">자세히 보기 <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>모바일 앱</h3>
                    <p>iOS와 Android 플랫폼을 위한 네이티브 및 하이브리드 앱을 개발합니다.</p>
                    <ul class="service-features">
                        <li>네이티브 앱 개발</li>
                        <li>크로스 플랫폼</li>
                        <li>앱 유지보수</li>
                    </ul>
                    <a href="#" class="service-link">자세히 보기 <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-cloud"></i>
                    </div>
                    <h3>클라우드 솔루션</h3>
                    <p>안정적이고 확장 가능한 클라우드 인프라를 구축하고 관리합니다.</p>
                    <ul class="service-features">
                        <li>AWS/Azure/GCP</li>
                        <li>서버 마이그레이션</li>
                        <li>DevOps 구축</li>
                    </ul>
                    <a href="#" class="service-link">자세히 보기 <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>보안 컨설팅</h3>
                    <p>기업의 정보 자산을 보호하는 종합 보안 솔루션을 제공합니다.</p>
                    <ul class="service-features">
                        <li>보안 진단</li>
                        <li>취약점 분석</li>
                        <li>보안 시스템 구축</li>
                    </ul>
                    <a href="#" class="service-link">자세히 보기 <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>데이터 분석</h3>
                    <p>빅데이터 분석과 AI 기술로 비즈니스 인사이트를 제공합니다.</p>
                    <ul class="service-features">
                        <li>데이터 시각화</li>
                        <li>예측 분석</li>
                        <li>머신러닝</li>
                    </ul>
                    <a href="#" class="service-link">자세히 보기 <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>IT 컨설팅</h3>
                    <p>전문가의 노하우로 최적의 IT 전략을 수립하고 실행합니다.</p>
                    <ul class="service-features">
                        <li>IT 전략 수립</li>
                        <li>프로세스 개선</li>
                        <li>디지털 트랜스포메이션</li>
                    </ul>
                    <a href="#" class="service-link">자세히 보기 <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section class="portfolio-section section-padding">
        <div class="container">
            <div class="section-header text-center">
                <span class="section-label">PORTFOLIO</span>
                <h2 class="section-title">성공적인 프로젝트</h2>
                <p class="section-description">다양한 산업 분야에서 검증된 솔루션을 제공합니다</p>
            </div>
            <div class="portfolio-grid">
                <div class="portfolio-item">
                    <div class="portfolio-image">
                        <div class="portfolio-overlay">
                            <div class="portfolio-content">
                                <h3>E-커머스 플랫폼</h3>
                                <p>월 거래액 10억원 규모의 쇼핑몰 구축</p>
                                <a href="#" class="btn btn-primary btn-small">상세보기</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="portfolio-item">
                    <div class="portfolio-image">
                        <div class="portfolio-overlay">
                            <div class="portfolio-content">
                                <h3>기업용 ERP 시스템</h3>
                                <p>통합 업무 관리 솔루션 개발</p>
                                <a href="#" class="btn btn-primary btn-small">상세보기</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="portfolio-item">
                    <div class="portfolio-image">
                        <div class="portfolio-overlay">
                            <div class="portfolio-content">
                                <h3>모바일 헬스케어 앱</h3>
                                <p>건강 관리 플랫폼 구축</p>
                                <a href="#" class="btn btn-primary btn-small">상세보기</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="portfolio-item">
                    <div class="portfolio-image">
                        <div class="portfolio-overlay">
                            <div class="portfolio-content">
                                <h3>스마트 팩토리</h3>
                                <p>제조업 디지털화 솔루션</p>
                                <a href="#" class="btn btn-primary btn-small">상세보기</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section section-padding bg-gradient">
        <div class="container">
            <div class="section-header text-center text-white">
                <span class="section-label">CONTACT</span>
                <h2 class="section-title">프로젝트 상담</h2>
                <p class="section-description">최고의 IT 솔루션으로 비즈니스 성장을 도와드리겠습니다</p>
            </div>
            <div class="contact-content">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h4>주소</h4>
                            <p>서울특별시 강남구 테헤란로 123</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h4>전화</h4>
                            <p>02-1234-5678</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>이메일</h4>
                            <p>contact@wenit.co.kr</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h4>근무시간</h4>
                            <p>평일 09:00 - 18:00</p>
                        </div>
                    </div>
                </div>
                <div class="contact-form-wrapper">
                    <form class="contact-form" id="contactForm">
                        <div class="form-row">
                            <div class="form-group">
                                <input type="text" placeholder="이름" required>
                            </div>
                            <div class="form-group">
                                <input type="email" placeholder="이메일" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="text" placeholder="회사명">
                        </div>
                        <div class="form-group">
                            <input type="tel" placeholder="연락처" required>
                        </div>
                        <div class="form-group">
                            <textarea rows="5" placeholder="문의 내용을 입력해주세요" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">상담 신청하기</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/layouts/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/js/main.js"></script>
</body>
</html>
