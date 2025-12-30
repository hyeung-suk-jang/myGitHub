from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from typing import List
import models
import schemas
from database import get_db
from auth import get_current_user

router = APIRouter(prefix="/api/payments", tags=["payments"])

# 플랫폼 수수료 비율 (30%)
PLATFORM_FEE_RATE = 0.30

@router.post("/", response_model=schemas.Payment)
def create_payment(
    payment: schemas.PaymentCreate,
    current_user: models.User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    # Get book
    book = db.query(models.Book).filter(models.Book.id == payment.book_id).first()
    if not book:
        raise HTTPException(status_code=404, detail="Book not found")

    # Check if book is published
    if not book.is_published:
        raise HTTPException(status_code=400, detail="Book is not available for purchase")

    # Check if user already purchased the book
    existing_payment = db.query(models.Payment).filter(
        models.Payment.user_id == current_user.id,
        models.Payment.book_id == payment.book_id,
        models.Payment.payment_status == "completed"
    ).first()

    if existing_payment:
        raise HTTPException(status_code=400, detail="You have already purchased this book")

    # Check if author is trying to buy their own book
    if current_user.id == book.author_id:
        raise HTTPException(status_code=400, detail="You cannot purchase your own book")

    # Calculate fees and revenue
    amount = book.price
    platform_fee = amount * PLATFORM_FEE_RATE
    author_revenue = amount - platform_fee

    # Create payment record
    db_payment = models.Payment(
        user_id=current_user.id,
        book_id=payment.book_id,
        amount=amount,
        platform_fee=platform_fee,
        author_revenue=author_revenue,
        payment_method=payment.payment_method,
        payment_status="completed"
    )
    db.add(db_payment)

    # Update author's balance
    author = db.query(models.User).filter(models.User.id == book.author_id).first()
    author.balance += author_revenue

    db.commit()
    db.refresh(db_payment)

    return db_payment

@router.get("/my-payments", response_model=List[schemas.Payment])
def list_my_payments(
    current_user: models.User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    payments = db.query(models.Payment).filter(
        models.Payment.user_id == current_user.id
    ).all()
    return payments

@router.get("/my-earnings")
def get_my_earnings(
    current_user: models.User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    if current_user.user_type != "author":
        raise HTTPException(status_code=403, detail="Only authors can view earnings")

    # Get all payments for author's books
    earnings = db.query(models.Payment).join(
        models.Book, models.Payment.book_id == models.Book.id
    ).filter(
        models.Book.author_id == current_user.id,
        models.Payment.payment_status == "completed"
    ).all()

    total_earnings = sum(payment.author_revenue for payment in earnings)
    total_sales = len(earnings)

    return {
        "total_earnings": total_earnings,
        "current_balance": current_user.balance,
        "total_sales": total_sales,
        "earnings": earnings
    }

@router.get("/purchased-books")
def get_purchased_books(
    current_user: models.User = Depends(get_current_user),
    db: Session = Depends(get_db)
):
    # Get all books purchased by current user
    payments = db.query(models.Payment).filter(
        models.Payment.user_id == current_user.id,
        models.Payment.payment_status == "completed"
    ).all()

    book_ids = [payment.book_id for payment in payments]
    books = db.query(models.Book).filter(models.Book.id.in_(book_ids)).all()

    return books
