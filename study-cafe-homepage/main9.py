"""
Study Cafe Homepage V9 - Carousel Theme
캐러셀 중심 테마 버전
"""

from fastapi import FastAPI, Request
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from fastapi.middleware.cors import CORSMiddleware
from app.api import router as api_router
import uvicorn

app = FastAPI(title="Study Cafe V9 - Carousel", version="9.0.0")
app.add_middleware(CORSMiddleware, allow_origins=["*"], allow_credentials=True, allow_methods=["*"], allow_headers=["*"])
app.mount("/static", StaticFiles(directory="static"), name="static")
templates = Jinja2Templates(directory="templates")
app.include_router(api_router, prefix="/api")

@app.get("/")
async def home(request: Request):
    return templates.TemplateResponse("index9.html", {"request": request})

if __name__ == "__main__":
    uvicorn.run("main9:app", host="0.0.0.0", port=8009, reload=True)
