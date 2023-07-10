<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategoryActionRequest;
use App\Models\Category;

class CategoryActionController extends Controller
{
    public function __invoke(CategoryActionRequest $request, Category $category)
    {
        $category->fill([
            'is_visible'    => $request->validated('is_visible', $category->is_visible),
            'is_budgetable' => $request->validated('is_budgetable', $category->is_budgetable),
            'is_reportable' => $request->validated('is_reportable', $category->is_reportable),
        ]);

        $category->save();
    }
}
