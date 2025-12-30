from fastapi import FastAPI, Request
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from fastapi.middleware.cors import CORSMiddleware
from pathlib import Path
import models
from database import engine
from routes import users, books, payments, settlements

# Create database tables
models.Base.metadata.create_all(bind=engine)

app = FastAPI(title="Fairy Tale Platform API")

# CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Mount static files and templates
frontend_path = Path(__file__).parent.parent / "frontend"
app.mount("/static", StaticFiles(directory=str(frontend_path / "static")), name="static")
templates = Jinja2Templates(directory=str(frontend_path / "templates"))

# Include routers
app.include_router(users.router)
app.include_router(books.router)
app.include_router(payments.router)
app.include_router(settlements.router)

@app.get("/")
async def index(request: Request):
    return templates.TemplateResponse("index.html", {"request": request})

@app.get("/login")
async def login_page(request: Request):
    return templates.TemplateResponse("login.html", {"request": request})

@app.get("/register")
async def register_page(request: Request):
    return templates.TemplateResponse("register.html", {"request": request})

@app.get("/author/dashboard")
async def author_dashboard(request: Request):
    return templates.TemplateResponse("author_dashboard.html", {"request": request})

@app.get("/reader/dashboard")
async def reader_dashboard(request: Request):
    return templates.TemplateResponse("reader_dashboard.html", {"request": request})

@app.get("/book/upload")
async def book_upload(request: Request):
    return templates.TemplateResponse("book_upload.html", {"request": request})

@app.get("/book/{book_id}")
async def book_view(request: Request, book_id: int):
    return templates.TemplateResponse("book_view.html", {"request": request, "book_id": book_id})

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)
