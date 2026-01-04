"""
Study Cafe Homepage V4 - Neon Cyberpunk Theme
네온 사이버펑크 테마 버전
"""

from fastapi import FastAPI, Request
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from fastapi.middleware.cors import CORSMiddleware
from app.api import router as api_router
import uvicorn

app = FastAPI(title="Study Cafe V4 - Neon Cyberpunk", version="4.0.0")
app.add_middleware(CORSMiddleware, allow_origins=["*"], allow_credentials=True, allow_methods=["*"], allow_headers=["*"])
app.mount("/static", StaticFiles(directory="static"), name="static")
templates = Jinja2Templates(directory="templates")
app.include_router(api_router, prefix="/api")

@app.get("/")
async def home(request: Request):
    return templates.TemplateResponse("index4.html", {"request": request})

if __name__ == "__main__":
    uvicorn.run("main4:app", host="0.0.0.0", port=8004, reload=True)
