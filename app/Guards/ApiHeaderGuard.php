<?php


namespace App\Guards;


use App\Authentication\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Route;

class ApiHeaderGuard implements Guard {
    use GuardHelpers;
    private $storageKey = '';
    private $request;
    private $inputKey = '1';


    public function __construct (UserProvider $provider, Request $request, $configuration) {
        $this->provider = $provider;
        $this->request = $request;
    }

    public function user(): ?Authenticatable
    {

        if (!is_null($this->user)) {
            return $this->user;
        }


        if(app()->runningInConsole()){
            $user = new User([
                'id' => 'console-attempt',
                'name' => 'Console'
            ]);
        } elseif(strpos(request()->getRequestUri(), 'doc') != false){
            return new User([
                'id' => 'web-attempt',
                'name' => 'Guest'
            ]);
        } else {
            $user = $this->provider->retrieveById(null);
        }
        if(!$user->getAuthIdentifier()){
            throw new AuthenticationException();
        }

        return $this->user = $user;
    }

    public function getTokenForRequest () {
        return $this->request->bearerToken();
    }

    public function validate(array $credentials = []): bool
    {
        if (empty($credentials[$this->inputKey])) {
            return false;
        }

        $credentials = [ $this->storageKey => $credentials[$this->inputKey] ];

        if ($this->provider->retrieveByCredentials($credentials)) {
            return true;
        }

        return false;
    }
}
