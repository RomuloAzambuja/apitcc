<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\support\Facades\Validator;
use Illuminate\support\Facades\Auth;


use Intervention\Image\Facades\Image;



use App\Models\User;
use App\Models\UserAppointment;
use App\Models\UserFavorite;
use App\Models\Society;
use App\Models\SocietyServices;



class UserController extends Controller
{
    private $loggedUser;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function read(){
        $array = ['error' => ''];

        $info = $this->loggedUser;
        $info['avatar']= url('media/avatars/'.$info['avatar']);
        $array['data']=$info;

        return $array;
    }

    public function toggleFavorite(Request $request){
        $array = ['error'=>''];

        $id_society =  $request->input('society');

        $society = Society::find($id_society);

        if ($society) {
            //verifica se o barbeiro esta cadastrado
            $fav = UserFavorite::select()
                ->where('id_user', $this->loggedUser->id)
                ->where('id_society', $id_society)
            ->first();

            if ($fav) {
                //remover favorito
                $fav->delete();
                $array['have'] = false;
            }else {
                 //adicionar favorito
                 $newFav = new UserFavorite();
                 $newFav->id_uer = $this->loggedUser->id;
                 $newFav->id_society = $id_society;
                 $newFav->save();
                 $array['have'] = true;
            }

        }else {
            $array['error'] = 'Empresa  inexistente';
        }


        return $array;
    }
    public function getFavorites(){
        $array = ['error'=> '', 'list'=>[]];

        $favs = UserFavorite::select()
            ->where('id_user', $this->loggedUser->id)
        ->get();

        if ($favs) {
            foreach ($favs as $fav) {
                $society = Society::find($fav['id_society']);
                $society['avatar']= url('media/avatars/'.$society['avatar']);
                $array['list'][]= $society;
            }
        }
        return $array;
    }

    //listanto os agendamentos
    public function getAppointments(){
        $array = ['error'=>'', 'list'=>[]];

        $apps = UserAppointment::select()
            ->where('id_user', $this->loggedUser->id)
            ->orderBy('ap_datetime', 'DESC')
        ->get();

        if ($apps) {

            foreach($apps as $app){
                $society = Society::find($app['id_soci$society']);
                $society['avatar']= url('media/avatars'.$society['avatar']);

                $service = SocietyServices::find($app['id_service']);

                $array['list'][] = [
                    'id' =>$app['id'],
                    'datetime'=>$app['ap_datetime'],
                    'society'=>$society,
                    'service'=> $service
                ];

            }
        }
        return $array;
    }

    public function update(Request $request){
        $array = ['error'=> ''];

        $rules = [
            'name' => 'min:2',
            'email' => 'email|unique:users',
            'password' => 'same:password_confirm',
            'password_confirm' => 'same:password'
        ];

        //validando as informações
        $validator = Validator::make($request->all(), $rules);

        if($validator->fails()){
            //erro em messages??
           //$array['error'] = $validator->messages();
            return $array;
        }

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $password_confirm = $request->input('password_confirm');

        $user = User::find($this->loggedUser->id);

        if($name){
            $user->name = $name;
        }

        if($email){
            $user->email= $email;
        }
        if($password){
           $user->password = password_hash($password, PASSWORD_DEFAULT);
        }
        $user->save();

        return $array;
    }
    /*
    erro images
    public function updateAvatar(Request $request) {
        $array = ['error'=>''];

        $rules = [
            'avatar' => 'required|image|mimes:png,jpg,jpeg'
        ];
        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()) {
            $array['error'] = $validator->messages();
            return $array;
        }

        $avatar = $request->file('avatar');

        $dest = public_path('/media/avatars');
        $avatarName = md5(time().rand(0,9999)).'.jpg';

        $img = Image::make($avatar->getRealPath());
        $img->fit(300, 300)->save($dest.'/'.$avatarName);

        $user = User::find($this->loggedUser->id);
        $user->avatar = $avatarName;
        $user->save();

        return $array;
    }*/

}
