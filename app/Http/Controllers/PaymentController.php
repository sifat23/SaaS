<?php

namespace App\Http\Controllers;

use App\Enums\ShopStatus;
use App\Repositories\Interfaces\ShopRegistrationRepositoryInterface;
use App\Repositories\Interfaces\ShopRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    protected $shopRegistrationRepo;
    protected $userRepo;
    protected $shopRepo;

    public function __construct(
        ShopRegistrationRepositoryInterface $shopRegistrationRepo,
        UserRepositoryInterface $userRepo,
        ShopRepositoryInterface $shopRepo,
    ) {
        $this->shopRegistrationRepo = $shopRegistrationRepo;
        $this->userRepo = $userRepo;
        $this->shopRepo = $shopRepo;
    }

    public function checkStatus(Request $request)
    {
        $sessionId = $request->query('session_id');

        $flag = 0;

        $registration = $this->shopRegistrationRepo->find('stripe_session_id', $sessionId);
        if (!empty($registration)) {
            $flag++;
        } else {
            return Inertia::render('Auth/ShopRegistrationSuccess', [
                'session_id' => $sessionId,
                'completed' => false,
                'message' => 'Shop registration not found'
            ]);
        }

        $user = $this->userRepo->find('email', $registration->owner_email);
        if (!empty($user)) {
            $flag++;
        } else {
            return Inertia::render('Auth/ShopRegistrationSuccess', [
                'session_id' => $sessionId,
                'completed' => false,
                'message' => 'User not found'
            ]);
        }

        // Adjust query to your real model/column name
        $shop = $this->shopRepo->find('owner_id', $user->id);
        if (empty($shop)) {
            return Inertia::render('Auth/ShopRegistrationSuccess', [
                'session_id'   => $sessionId,
                'completed' => false,
                'message'   => 'Shop not found',
            ]);
        } else {
            $flag++;
        }

        $completed = $shop->status ===  ShopStatus::ACTIVE && $flag === 3;

        return Inertia::render('Auth/ShopRegistrationSuccess', [
            'session_id'   => $sessionId,
            'completed' => $completed,
            'message' => 'Shop registration complete'
        ]);
    }

    public function canceled(Request $request)
    {
        $sessionId = $request->query('session_id');

        return Inertia::render('Auth/ShopRegistrationCancel', [
            'session_id' => $sessionId,
        ]);
    }
}
