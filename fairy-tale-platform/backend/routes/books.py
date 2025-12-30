from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from typing import List
import models
import schemas
from database import get_db
from auth import get_current_user, require_author

router = APIRouter(prefix="/api/books", tags=["books"])

@router.post("/", response_model=schemas.Book)
def create_book(
    book: schemas.BookCreate,
    current_user: models.User = Depends(require_author),
    db: Session = Depends(get_db)
):
    db_book = models.Book(
        title=book.title,
        description=book.description,
        author_id=current_user.id,
        price=book.price,
        cover_image=book.cover_image,
        content=book.content
    )
    db.add(db_book)
    db.commit()
    db.refresh(db_book)
    return db_book

@router.get("/", response_model=List[schemas.Book])
def list_books(
    skip: int = 0,
    limit: int = 20,
    db: Session = Depends(get_db)
):
    books = db.query(models.Book).filter(
        models.Book.is_published == True
    ).offset(skip).limit(limit).all()
    return books

@router.get("/my-books", response_model=List[schemas.Book])
def list_my_books(
    current_user: models.User = Depends(require_author),
    db: Session = Depends(get_db)
):
    books = db.query(models.Book).filter(
        models.Book.author_id == current_user.id
    ).all()
    return books

@router.get("/{book_id}", response_model=schemas.Book)
def get_book(book_id: int, db: Session = Depends(get_db)):
    book = db.query(models.Book).filter(models.Book.id == book_id).first()
    if not book:
        raise HTTPException(status_code=404, detail="Book not found")
    return book

@router.get("/{book_id}/content")
def get_book_content(
    book_id: int,
    current_user: models.User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    book = db.query(models.Book).filter(models.Book.id == book_id).first()
    if not book:
        raise HTTPException(status_code=404, detail="Book not found")

    # Check if user has purchased the book or is the author
    if current_user.id == book.author_id:
        return {"content": book.content, "purchased": True}

    payment = db.query(models.Payment).filter(
        models.Payment.user_id == current_user.id,
        models.Payment.book_id == book_id,
        models.Payment.payment_status == "completed"
    ).first()

    if not payment:
        raise HTTPException(
            status_code=403,
            detail="You must purchase this book to read it"
        )

    # Increment views
    book.views += 1
    db.commit()

    return {"content": book.content, "purchased": True}

@router.put("/{book_id}", response_model=schemas.Book)
def update_book(
    book_id: int,
    book_update: schemas.BookUpdate,
    current_user: models.User = Depends(require_author),
    db: Session = Depends(get_db)
):
    book = db.query(models.Book).filter(models.Book.id == book_id).first()
    if not book:
        raise HTTPException(status_code=404, detail="Book not found")

    if book.author_id != current_user.id:
        raise HTTPException(status_code=403, detail="Not authorized to update this book")

    update_data = book_update.dict(exclude_unset=True)
    for field, value in update_data.items():
        setattr(book, field, value)

    db.commit()
    db.refresh(book)
    return book

@router.delete("/{book_id}")
def delete_book(
    book_id: int,
    current_user: models.User = Depends(require_author),
    db: Session = Depends(get_db)
):
    book = db.query(models.Book).filter(models.Book.id == book_id).first()
    if not book:
        raise HTTPException(status_code=404, detail="Book not found")

    if book.author_id != current_user.id:
        raise HTTPException(status_code=403, detail="Not authorized to delete this book")

    db.delete(book)
    db.commit()
    return {"message": "Book deleted successfully"}

@router.get("/{book_id}/purchased")
def check_purchased(
    book_id: int,
    current_user: models.User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    book = db.query(models.Book).filter(models.Book.id == book_id).first()
    if not book:
        raise HTTPException(status_code=404, detail="Book not found")

    # Author always has access
    if current_user.id == book.author_id:
        return {"purchased": True, "is_author": True}

    # Check if user purchased the book
    payment = db.query(models.Payment).filter(
        models.Payment.user_id == current_user.id,
        models.Payment.book_id == book_id,
        models.Payment.payment_status == "completed"
    ).first()

    return {"purchased": payment is not None, "is_author": False}
