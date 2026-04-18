<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrationRequest;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class RegistrationController extends Controller
{
    protected $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function index()
    {
        return Inertia::render('Auth/UserRegistration');
    }

    public function store(RegistrationRequest $request)
    {
        // 3. Use the Repository to insert data
        $user = $this->userRepo->create([
            'name' => $request->owner_name,
            'email' => $request->owner_email,
            'password' => $request->password
        ]);

        // 4. Return a standardized response
        return response()->json([
            'message' => 'User created successfully!',
            'data'    => $user
        ], 201);
    }


}
