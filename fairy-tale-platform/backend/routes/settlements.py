from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session
from typing import List
import models
import schemas
from database import get_db
from auth import get_current_user, require_author

router = APIRouter(prefix="/api/settlements", tags=["settlements"])

@router.post("/", response_model=schemas.Settlement)
def request_settlement(
    settlement: schemas.SettlementCreate,
    current_user: models.User = Depends(require_author),
    db: Session = Depends(get_db)
):
    # Check if author has enough balance
    if current_user.balance < settlement.amount:
        raise HTTPException(
            status_code=400,
            detail=f"Insufficient balance. Available: {current_user.balance}"
        )

    # Minimum settlement amount
    if settlement.amount < 10000:
        raise HTTPException(
            status_code=400,
            detail="Minimum settlement amount is 10,000 won"
        )

    # Create settlement request
    db_settlement = models.Settlement(
        author_id=current_user.id,
        amount=settlement.amount,
        bank_account=settlement.bank_account,
        notes=settlement.notes,
        status="pending"
    )
    db.add(db_settlement)

    # Deduct from author's balance
    current_user.balance -= settlement.amount

    db.commit()
    db.refresh(db_settlement)

    return db_settlement

@router.get("/my-settlements", response_model=List[schemas.Settlement])
def list_my_settlements(
    current_user: models.User = Depends(require_author),
    db: Session = Depends(get_db)
):
    settlements = db.query(models.Settlement).filter(
        models.Settlement.author_id == current_user.id
    ).order_by(models.Settlement.settlement_date.desc()).all()
    return settlements

@router.get("/{settlement_id}", response_model=schemas.Settlement)
def get_settlement(
    settlement_id: int,
    current_user: models.User = Depends(require_author),
    db: Session = Depends(get_db)
):
    settlement = db.query(models.Settlement).filter(
        models.Settlement.id == settlement_id
    ).first()

    if not settlement:
        raise HTTPException(status_code=404, detail="Settlement not found")

    if settlement.author_id != current_user.id:
        raise HTTPException(status_code=403, detail="Not authorized to view this settlement")

    return settlement
