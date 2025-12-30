"""
Router Module
PHP 스타일의 라우팅 시스템
"""

from typing import Callable, List, Dict, Optional
from fastapi import APIRouter, Request, Depends
from sqlalchemy.orm import Session
from core.database import get_db
import inspect


class Router:
    """라우터 관리 클래스 - PHP 라우팅과 유사한 방식"""

    def __init__(self, prefix: str = "", tags: Optional[List[str]] = None):
        self.router = APIRouter(prefix=prefix, tags=tags or [])
        self.routes: List[Dict] = []

    def get(self, path: str, controller: Callable, name: Optional[str] = None):
        """GET 라우트 등록"""
        return self._add_route("GET", path, controller, name)

    def post(self, path: str, controller: Callable, name: Optional[str] = None):
        """POST 라우트 등록"""
        return self._add_route("POST", path, controller, name)

    def put(self, path: str, controller: Callable, name: Optional[str] = None):
        """PUT 라우트 등록"""
        return self._add_route("PUT", path, controller, name)

    def delete(self, path: str, controller: Callable, name: Optional[str] = None):
        """DELETE 라우트 등록"""
        return self._add_route("DELETE", path, controller, name)

    def patch(self, path: str, controller: Callable, name: Optional[str] = None):
        """PATCH 라우트 등록"""
        return self._add_route("PATCH", path, controller, name)

    def _add_route(
        self,
        method: str,
        path: str,
        controller: Callable,
        name: Optional[str] = None
    ):
        """라우트 추가"""
        # 컨트롤러가 클래스 메서드인 경우 자동으로 의존성 주입
        async def endpoint(
            request: Request,
            db: Session = Depends(get_db),
            **kwargs
        ):
            # 컨트롤러 인스턴스 생성 및 의존성 주입
            if inspect.ismethod(controller):
                # 이미 바인딩된 메서드
                ctrl = controller.__self__
            else:
                # 클래스 인스턴스 메서드
                ctrl = controller

            # BaseController를 상속받은 경우 자동 주입
            if hasattr(ctrl, 'set_request') and hasattr(ctrl, 'set_db'):
                ctrl.set_request(request).set_db(db)

            # 컨트롤러 실행
            if inspect.iscoroutinefunction(controller):
                return await controller(**kwargs)
            else:
                return controller(**kwargs)

        # 라우트 정보 저장
        route_info = {
            "method": method,
            "path": path,
            "controller": controller,
            "name": name or f"{method.lower()}_{path.replace('/', '_')}"
        }
        self.routes.append(route_info)

        # FastAPI 라우터에 등록
        route_method = getattr(self.router, method.lower())
        route_method(path, name=route_info["name"])(endpoint)

        return self

    def group(self, prefix: str, routes_func: Callable):
        """라우트 그룹화 - PHP의 Route::group과 유사"""
        grouped_router = Router(prefix=prefix)
        routes_func(grouped_router)
        return grouped_router

    def resource(self, name: str, controller_class):
        """
        RESTful 리소스 라우팅 - PHP Laravel의 Route::resource와 유사
        자동으로 CRUD 라우트 생성
        """
        controller = controller_class()

        # RESTful 라우트 매핑
        resource_routes = [
            ("GET", f"/{name}", "index"),
            ("POST", f"/{name}", "store"),
            ("GET", f"/{name}/{{id}}", "show"),
            ("PUT", f"/{name}/{{id}}", "update"),
            ("DELETE", f"/{name}/{{id}}", "destroy"),
        ]

        for method, path, action in resource_routes:
            if hasattr(controller, action):
                handler = getattr(controller, action)
                self._add_route(method, path, handler, f"{name}.{action}")

        return self

    def include(self, router: 'Router'):
        """다른 라우터 포함"""
        self.router.include_router(router.router)
        return self

    def get_router(self) -> APIRouter:
        """FastAPI 라우터 반환"""
        return self.router
