"""
Post Controller
게시글 관리 컨트롤러
"""

from core.controller import BaseController
from app.models.post import Post
from app.schemas.post_schema import PostCreate, PostUpdate


class PostController(BaseController):
    """게시글 컨트롤러 - CRUD 작업"""

    def index(self, page: int = 1, limit: int = 15):
        """게시글 목록 조회"""
        try:
            posts = self.db.query(Post).offset((page - 1) * limit).limit(limit).all()
            total = self.db.query(Post).count()

            return self.success({
                "posts": [post.to_dict() for post in posts],
                "pagination": {
                    "page": page,
                    "limit": limit,
                    "total": total
                }
            })
        except Exception as e:
            return self.error(f"Failed to fetch posts: {str(e)}", 500)

    def show(self, id: int):
        """게시글 상세 조회"""
        try:
            post = self.db.query(Post).filter(Post.id == id).first()
            if not post:
                return self.error("Post not found", 404)
            return self.success(post.to_dict())
        except Exception as e:
            return self.error(f"Failed to fetch post: {str(e)}", 500)

    def store(self, data: PostCreate):
        """게시글 생성"""
        try:
            post = Post(
                title=data.title,
                content=data.content,
                user_id=data.user_id
            )
            self.db.add(post)
            self.db.commit()
            self.db.refresh(post)

            return self.success(post.to_dict(), "Post created successfully")
        except Exception as e:
            self.db.rollback()
            return self.error(f"Failed to create post: {str(e)}", 500)

    def update(self, id: int, data: PostUpdate):
        """게시글 수정"""
        try:
            post = self.db.query(Post).filter(Post.id == id).first()
            if not post:
                return self.error("Post not found", 404)

            if data.title is not None:
                post.title = data.title
            if data.content is not None:
                post.content = data.content

            self.db.commit()
            self.db.refresh(post)

            return self.success(post.to_dict(), "Post updated successfully")
        except Exception as e:
            self.db.rollback()
            return self.error(f"Failed to update post: {str(e)}", 500)

    def destroy(self, id: int):
        """게시글 삭제"""
        try:
            post = self.db.query(Post).filter(Post.id == id).first()
            if not post:
                return self.error("Post not found", 404)

            self.db.delete(post)
            self.db.commit()

            return self.success(None, "Post deleted successfully")
        except Exception as e:
            self.db.rollback()
            return self.error(f"Failed to delete post: {str(e)}", 500)
