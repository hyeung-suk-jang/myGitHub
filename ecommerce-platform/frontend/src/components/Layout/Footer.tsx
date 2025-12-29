const Footer = () => {
  return (
    <footer className="bg-gray-800 text-white mt-auto">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div>
            <h3 className="text-lg font-semibold mb-4">고객센터</h3>
            <p className="text-gray-400">1588-0000</p>
            <p className="text-gray-400 text-sm mt-2">
              평일 09:00 - 18:00
              <br />
              주말, 공휴일 휴무
            </p>
          </div>

          <div>
            <h3 className="text-lg font-semibold mb-4">쇼핑 정보</h3>
            <ul className="space-y-2 text-gray-400">
              <li>주문/배송 조회</li>
              <li>취소/반품/교환</li>
              <li>FAQ</li>
            </ul>
          </div>

          <div>
            <h3 className="text-lg font-semibold mb-4">회사 정보</h3>
            <ul className="space-y-2 text-gray-400">
              <li>회사소개</li>
              <li>이용약관</li>
              <li>개인정보처리방침</li>
            </ul>
          </div>

          <div>
            <h3 className="text-lg font-semibold mb-4">SNS</h3>
            <div className="flex space-x-4">
              <a href="#" className="text-gray-400 hover:text-white">
                Instagram
              </a>
              <a href="#" className="text-gray-400 hover:text-white">
                Facebook
              </a>
            </div>
          </div>
        </div>

        <div className="border-t border-gray-700 mt-8 pt-8 text-sm text-gray-400">
          <p>© 2024 자사 쇼핑몰. All rights reserved.</p>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
