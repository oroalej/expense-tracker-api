<?php

namespace App\Enums;

enum AccountDebtState: int
{
    case AutoLoan = 1;
    case EducationalLoan = 2;
    case PersonalLoan = 3;
    case MedicalDebt = 4;
    case Mortgage = 5;
    case CreditCard = 6;
    case LineOfCredit = 7;
    case OtherDebt = 8;
}
