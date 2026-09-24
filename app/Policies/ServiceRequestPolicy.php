<?php

namespace App\Policies;

use App\Models\Business\BusinessUser;
use App\Models\ServiceRequest;

class ServiceRequestPolicy
{
    public function view(BusinessUser $user, ServiceRequest $request): bool
    {
        // Admin/supervisor can view any request; users only their own
        if ($user->isAdmin()) return true;
        return $request->user_id === $user->id;
    }

    public function create(BusinessUser $user): bool
    {
        return !$user->isAdmin();
    }

    public function update(BusinessUser $user, ServiceRequest $request): bool
    {
        return $user->isAdmin();
    }

    public function delete(BusinessUser $user, ServiceRequest $request): bool
    {
        return $user->role === 'admin';
    }
}