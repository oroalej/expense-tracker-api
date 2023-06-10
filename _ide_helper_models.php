<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Account
 *
 * @property int $id
 * @property int $account_type_id
 * @property int $ledger_id
 * @property string $name
 * @property bool $is_archived
 * @property float $current_balance
 * @property Carbon|null $archived_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property AccountType accountType
 * @method static AccountFactory factory()
 * @property-read \App\Models\AccountType $accountType
 * @property-read \App\Models\Ledger $ledger
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Account newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Account query()
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereAccountTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereArchivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereCurrentBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereIsArchived($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereLedgerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Account withoutTrashed()
 */
	class Account extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AccountType
 *
 * @property int $id
 * @property int $group_type_id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Term $accountGroupType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Account> $accounts
 * @property-read int|null $accounts_count
 * @method static \Illuminate\Database\Eloquent\Builder|AccountType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountType query()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountType whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountType whereGroupTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountType whereUpdatedAt($value)
 */
	class AccountType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Budget
 *
 * @property int $id
 * @property int $ledger_id
 * @property int $month
 * @property int $year
 * @property Carbon $date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static BudgetFactory factory()
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BudgetCategory> $budgetCategories
 * @property-read int|null $budget_categories_count
 * @property-read \App\Models\Ledger $ledger
 * @method static \Illuminate\Database\Eloquent\Builder|Budget filterByMonthYearAndLedgerId(int $month, int $year, int $ledgerId)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Budget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Budget onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Budget query()
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereLedgerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget whereYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Budget withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Budget withoutTrashed()
 */
	class Budget extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BudgetCategory
 *
 * @property int $id
 * @property int $ledger_id
 * @property int $category_id
 * @property int $assigned
 * @property int $available
 * @property int $activity
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|BudgetCategory summarySelect()
 * @method static BudgetCategoryFactory factory()
 * @property int $budget_id
 * @property string|null $deleted_at
 * @property-read \App\Models\Budget $budget
 * @property-read \App\Models\Category $category
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetCategory whereActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetCategory whereAssigned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetCategory whereAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetCategory whereBudgetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetCategory whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetCategory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BudgetCategory whereId($value)
 */
	class BudgetCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Category
 *
 * @property int $id
 * @property string $hashid
 * @property int $category_group_id
 * @property int $ledger_id
 * @property string $name
 * @property string $notes
 * @property int $order
 * @property bool $is_hidden
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static CategoryFactory factory()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BudgetCategory> $budgetCategories
 * @property-read int|null $budget_categories_count
 * @property-read \App\Models\CategoryGroup $categoryGroup
 * @property-read \App\Models\Ledger $ledger
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCategoryGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereHashid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereIsHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereLedgerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Category withoutTrashed()
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CategoryGroup
 *
 * @property int $id
 * @property string $hashid
 * @property int $ledger_id
 * @property string $name
 * @property string $notes
 * @property int $order
 * @property bool $is_hidden
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static CategoryGroupFactory factory()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Category> $categories
 * @property-read int|null $categories_count
 * @property-read \App\Models\Ledger $ledger
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryGroup onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryGroup whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryGroup whereHashid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryGroup whereIsHidden($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryGroup whereLedgerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryGroup whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryGroup whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryGroup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryGroup withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryGroup withoutTrashed()
 */
	class CategoryGroup extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Currency
 *
 * @property int $id
 * @property string $name
 * @property string $abbr
 * @property string $code
 * @property string $locale
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ledger> $ledgers
 * @property-read int|null $ledgers_count
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency query()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereAbbr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Currency whereUpdatedAt($value)
 */
	class Currency extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Debt
 *
 * @property int $id
 * @property int $ledger_id
 * @property string $name
 * @property string $notes
 * @property float $current_balance
 * @property float $interest_rate
 * @property float $min_payment_amount
 * @property Carbon|null $closed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static DebtFactory factory()
 * @property-read \App\Models\Ledger $ledger
 * @property-read \App\Models\Term|null $paymentInterval
 * @method static \Illuminate\Database\Eloquent\Builder|Debt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Debt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Debt onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Debt query()
 * @method static \Illuminate\Database\Eloquent\Builder|Debt withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Debt withoutTrashed()
 */
	class Debt extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Goal
 *
 * @property int $id
 * @property int $ledger_id
 * @property string $name
 * @property string $notes
 * @property float $current_balance
 * @property float $interest_rate
 * @property float $min_payment_amount
 * @property Carbon|null $closed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static GoalFactory factory()
 * @property-read \App\Models\Ledger $ledger
 * @method static \Illuminate\Database\Eloquent\Builder|Goal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Goal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Goal onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Goal query()
 * @method static \Illuminate\Database\Eloquent\Builder|Goal withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Goal withoutTrashed()
 */
	class Goal extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Ledger
 *
 * @property int $id
 * @property int $user_id
 * @property string $number_format
 * @property int $currency_id
 * @property string $name
 * @property bool $is_archived
 * @property Carbon|null $archived_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static LedgerFactory factory()
 * @property string $date_format
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Account> $accounts
 * @property-read int|null $accounts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Budget> $budgets
 * @property-read int|null $budgets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Category> $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CategoryGroup> $categoryGroups
 * @property-read int|null $category_groups_count
 * @property-read \App\Models\Currency $currency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Ledger newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ledger newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ledger onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Ledger query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ledger whereArchivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ledger whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ledger whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ledger whereDateFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ledger whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ledger whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ledger whereIsArchived($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ledger whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ledger whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ledger whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ledger withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Ledger withoutTrashed()
 */
	class Ledger extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Model
 *
 * @property int $id
 * @mixin Builder
 * @mixin Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|Model newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Model newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Model query()
 */
	class Model extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Taxonomy
 *
 * @property int $id
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Term> $terms
 * @property-read int|null $terms_count
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy query()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereName($value)
 */
	class Taxonomy extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Term
 *
 * @property-read string $name
 * @property-read int taxonomy_id
 * @property int $id
 * @property int $taxonomy_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AccountType> $accountTypes
 * @property-read int|null $account_types_count
 * @property-read \App\Models\Taxonomy $taxonomy
 * @method static \Illuminate\Database\Eloquent\Builder|Term newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Term newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Term query()
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Term whereTaxonomyId($value)
 */
	class Term extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Transaction
 *
 * @property int $id
 * @property int $account_id
 * @property int $category_id
 * @property float $inflow
 * @property float $outflow
 * @property string $remarks
 * @property Carbon $transaction_date
 * @property bool $is_approved
 * @property bool $is_cleared
 * @property Category $category
 * @property Account $account
 * @property Carbon|null $approved_at
 * @property Carbon|null $rejected_at
 * @property Carbon|null $cleared_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Transaction defaultSelect()
 * @method static Builder|Transaction basicSelect()
 * @method static Builder| \Illuminate\Database\Query\Builder|Transaction filterByLedgerTransactionDateAndOptionalCategoryId(int $ledgerId, int $month, int $year, int $categoryId = null)
 * @method static Builder| \Illuminate\Database\Query\Builder|Transaction filterByAccountOrCategory(int $accountId = null, int $categoryId = null)
 * @method static TransactionFactory factory()
 * @property int|null $related_id
 * @property int $ledger_id
 * @property bool $is_excluded
 * @property-read \App\Models\Ledger $ledger
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction filterByAccountOrCategory(?int $accountId = null, ?int $categoryId = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction filterByLedgerTransactionDateAndOptionalCategoryId(int $ledgerId, int $month, int $year, ?int $categoryId = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereClearedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereInflow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereIsApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereIsCleared($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereIsExcluded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereLedgerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereOutflow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereRejectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereRelatedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereTransactionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Transaction withoutTrashed()
 */
	class Transaction extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $email
 * @property string $name
 * @property string $password
 * @property string $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder newModelQuery()
 * @method static Builder newQuery()
 * @method static Builder query()
 * @method static UserFactory factory()
 * @mixin Eloquent
 * @property string $uuid
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Account> $accounts
 * @property-read int|null $accounts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Category> $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ledger> $ledgers
 * @property-read int|null $ledgers_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUuid($value)
 */
	class User extends \Eloquent {}
}

