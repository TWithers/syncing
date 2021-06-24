<?php
class ExampleBlackboardApiController
{
    protected \Sync\Integrations\Blackboard\ApiConsumer $api;
    protected \Sync\Repositories\ClassRepository $classRepo;
    protected \Sync\Repositories\UserRepository $userRepo;
    protected \Sync\SyncHandler $sync;

    public function __construct(CacheProvider $cacheProvider, \Sync\Repositories\ClassRepository  $classRepo, \Sync\Repositories\UserRepository $userRepo){
        $this->classRepo = $classRepo;
        $this->userRepo = $userRepo;

        $this->middleware(function ($request, $next) use ($cacheProvider) {
            $token = $cacheProvider->load('blackboard'.$this->user->id,function($item){
                throw new Exception('OAuth Token does not exist');
            });
            $this->api = new \Sync\Integrations\Blackboard\ApiConsumer($token, 'http://blackboard.com');
            $this->sync = new \Sync\SyncHandler($this->api,$this->classRepo, $this->userRepo);
            return $next($request);
        })->except('oauthCallback');
    }

    public function OAuthCallback(CacheProvider $cacheProvider){
        $handler = new \Sync\Integrations\Blackboard\OAuthHandler('myConsumerId', 'mySecretKey', 'localhost:8000/blackboard/oauth/callback', 'http://blackboard.com/oauth/token');
        $handler->handleRequest();
        $token = $handler->getToken();
        $cacheProvider->setItem('blackboard'.$this->user->id,function($item) use ($token){
            $item->setExpiration(3600);
            return $token;
        });
        return redirectTo('syncMenu');
    }

    public function fetchApiClasses(){
        return $this->api->getCourses();
    }

    public function syncClasses(Request $request){
        $this->sync->sync($request->request->get('courseIds'),$this->user->id);
        return $this->sync->getCounts();
    }
}

