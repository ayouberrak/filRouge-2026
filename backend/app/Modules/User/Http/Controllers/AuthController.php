<?php

namespace App\Modules\User\Http\Controllers;

use App\Modules\User\Application\UseCases\LoginUser;
use App\Modules\User\Application\UseCases\LogoutUser;
use App\Modules\User\Http\Requests\LoginRequest;
use App\Modules\User\Application\DTO\LoginDTO;
use Illuminate\Http\Request;

class AuthController
{
    private LoginUser $loginUser;
    private LogoutUser $logoutUser;

    public function __construct(LoginUser $loginUser, LogoutUser $logoutUser)
    {
        $this->loginUser = $loginUser;
        $this->logoutUser = $logoutUser;
    }

    public function login(LoginRequest $request)
    {
        $loginDTO = new LoginDTO(
            $request->email,
            $request->password
        );
        $result = $this->loginUser->execute($loginDTO);

        return response()->json([
            'access_token' => $result['token'],
            'token_type' => 'Bearer',
            'user' => [
                'id' => $result['user']->getId(),
                'first_name' => $result['user']->getFirstName(),
                'last_name' => $result['user']->getLastName(),
                'email' => $result['user']->getEmail(),
                'role' => $result['user']->getRole(),
                'status' => $result['user']->getStatus()
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $tokenStr = $request->bearerToken();
        $tokenId = $tokenStr ? explode('|', $tokenStr)[0] : '';
        
        if ($tokenId) {
            $this->logoutUser->execute($request->user()->id, $tokenId);
        }

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
