<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function store(Request $request): JsonResponse
    {
        // 2. Get validated data from the Form Request
        $validatedData = $request->validated();

        // 3. Use the Repository to insert data
        $user = $this->userRepo->create($validatedData);

        // 4. Return a standardized response
        return response()->json([
            'message' => 'User created successfully!',
            'data'    => $user
        ], 201);
    }


}
