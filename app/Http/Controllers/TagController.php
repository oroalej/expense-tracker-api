<?php

namespace App\Http\Controllers;

use App\Actions\Tag\CreateTag;
use App\Actions\Tag\UpdateTag;
use App\DataObject\TagData;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TagController extends Controller
{
	/**
	 * Store a newly created resource in storage.
	 *
	 * @param StoreTagRequest $request
	 * @return JsonResponse
	 * @throws AuthenticationException
	 * @throws Throwable
	 */
	public function store(StoreTagRequest $request): JsonResponse
	{
		$tagData = new TagData($request->name, $request->description);

		(new CreateTag($tagData))->execute();

		return response()->json([], Response::HTTP_CREATED);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param UpdateTagRequest $request
	 * @param Tag              $tag
	 * @return JsonResponse
	 */
	public function update(UpdateTagRequest $request, Tag $tag): JsonResponse
	{
		$tagData = new TagData($request->name, $request->description);

		(new UpdateTag($tag, $tagData))->execute();

		return response()->json([], Response::HTTP_OK);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param Tag $tag
	 * @return JsonResponse
	 */
	public function destroy(Tag $tag): JsonResponse
	{
		$tag->delete();

		return response()->json([], Response::HTTP_OK);
	}
}
