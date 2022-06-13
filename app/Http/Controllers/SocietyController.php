<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\UserFavorite;
use App\Models\UserAppointment;
use App\Models\SocietyPhotos;
use App\Models\SocietyAvailability;
use App\Models\Society;
use App\Models\SocietyServices;
use App\Models\SocietyTestimonial;

class SocietyController extends Controller
{
    private $loggedUser;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    //gerar empresas para teste
    /*public function createRandom() {
        $array = ['error'=>''];

            for($q=0; $q<15; $q++) {
                $names = ['Sociedade', 'Society', 'Sport', 'União', 'Sport', 'Centro', 'Arena',];
                $lastnames = [ 'Futebol', 'Clube', 'Da Bola', 'Futebol 7', 'Esportivo', 'Gol', 'Gold', 'Desportivo' ];

            $servicos = ['Reserva'];
            $servicos2 = ['quadra','campo', 'area de festa', 'mesa', 'quiosque','churraqueira'];

            $depos = [
                'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.',
                'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.',
                'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.',
                'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.',
                'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.'
            ];
            $newSociety = new Society();
            $newSociety->name = $names[rand(0, count($names)-1)].' '.$lastnames[rand(0, count($lastnames)-1)];
            $newSociety->avatar = rand(1, 4).'.png';
            $newSociety->stars = rand(2, 4).'.'.rand(0, 9);
            $newSociety->latitude = '-26.6'.rand(0, 9).'94101';
            $newSociety->longitude = '-49.0'.rand(0,9).'53245';
            $newSociety->save();

            $ns = rand(3, 6);

            for($w=0;$w<4;$w++) {
                $newSocietyPhoto = new SocietyPhotos();
                $newSocietyPhoto->id_society = $newSociety->id;
                $newSocietyPhoto->url = rand(1, 5).'.png';
                $newSocietyPhoto->save();
            }
            for($w=0;$w<$ns;$w++) {
                $newSocietyService = new SocietyServices();
                $newSocietyService->id_society = $newSociety->id;
                $newSocietyService->name = $servicos[rand(0, count($servicos)-1)].' de '.$servicos2[rand(0, count($servicos2)-1)];
                $newSocietyService->price = rand(1, 99).'.'.rand(0, 100);
                $newSocietyService->save();
            }
            for($w=0;$w<3;$w++) {
                $newSocietyTestimonial = new SocietyTestimonial();
                $newSocietyTestimonial->id_society = $newSociety->id;
                $newSocietyTestimonial->name = $names[rand(0, count($names)-1)];
                $newSocietyTestimonial->rate = rand(2, 4).'.'.rand(0, 9);
                $newSocietyTestimonial->body = $depos[rand(0, count($depos)-1)];
                $newSocietyTestimonial->save();
            }
            for($e=0;$e<4;$e++){
                $rAdd = rand(7, 10);
                $hours = [];
                for($r=0;$r<8;$r++) {
                    $time = $r + $rAdd;
                    if($time < 10) {
                        $time = '0'.$time;
                    }
                    $hours[] = $time.':00';
                }
                $newSocietyAvail = new SocietyAvailability();
                $newSocietyAvail->id_society = $newSociety->id;
                $newSocietyAvail->weekday = $e;
                $newSocietyAvail->hours = implode(',', $hours);
                $newSocietyAvail->save();
            }
        }
        return $array;
    }*/

    private function searchGeo($address){
        $key = env('MAPS_KEY', null);

        $address = urlencode($address);

        $url= 'https://maps.googleapis.com/maps/api/geocode/json?address='.$address.'&key='.$key;
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);

        return json_decode($res, true);
    }

    public function list(Request $request){
        $array = ['error'=>''];

        $lat= $request->imput('lat');
        $lng=$request->input('lng');
        $city=$request->input('city');
        $offset = $request->input('offset');
        if(!$offset){
            $offset = 0;
        }

        if (!empty($city)) {
            $res = $this->searchGeo($city);

            if(count($res['results'])>0){
                $lat = $res['results'][0]['geometry']['location']['lat'];
                $lng = $res['results'][0]['geometry']['location']['lng'];

            }
        }elseif (!empty($lat)&& !empty($lng)) {
            $res =  $this->searchGeo(($lat.','.$lng));

            if (count($res['results'])>0) {
                $city = $res['results'][0]['formatted_addres'];
            }
        }else {
            $lat='-26.6129085';
            $lng='-49.0137327,15';
            $city='Massaranduba';
        }

        $societys = Society::select(Society::raw('*, SQRT(
            POW(69.1*(latitude = '.$lat.'),2)+
            POW(69.1*('.$lng.' - longitude) * COS(latitude / 57.3),2)) AS distance'))
            ->havingRaw('distance <?',['10'])
            ->orderBy('distance','ASC')
            ->offset($offset)
            ->limit(5)
            ->get();


        foreach ($societys as $bkey => $bvalue){
            $societys[$bkey]['avatar'] = url('media/avatars/'.$societys[$bkey]['avatar']);
        }

        $array['data']=$societys;
        $array['loc']='Massaranduba';


        return $array;
    }
    public function one($id){
        $array =['error'=>''];

        $society = Society::find($id);

        if ($society) {
            $society['avatar'] = url('media/avatars/'.$society['avatar']);
            $society['favorited']= false;
            $society['photos']=[];
            $society['services']=[];
            $society['testimonials']=[];
            $society['available']=[];

            //verificando favoritos
            $cFavorite = UserFavorite::where('id_user', $this->loggedUser->id)
                ->where('id_society', $society->id)
                ->count();
            if ($cFavorite >0) {
                $society['favorited']=true;
            }

            //Fotos
            $society['photos'] = SocietyPhotos::select(['id','url'])
            ->where('id_society', $society->id)
            ->get();
            foreach ($society['photos']as $bpkey=>$bpvalue){
                $society['photos'][$bpkey]['url'] = url('media/uploads/'.$society['photos'][$bpkey]['url']);
            }
            //serviços
            $society['services'] = SocietyServices::select('id', 'name', 'price')
            ->where('id_society',$society->id)
            ->get();

            //depoimentos
            $society['testimonials'] = SocietyTestimonial::select('id','name','rate','body')
            ->where('id_society', $society->id)
            ->get();

            //disponibilidade
            $availability = [];

            // pegando a disponibilidade total
            $avails = SocietyAvailability::where('id_society', $society->$id)->get();
            $availWeekdays = [];
            foreach($avails as $item){
                $availWeekdays[$item['weekday']]= explode(',',$item['hours']);
            }

            // agendamentos dos proximos 20 dias
            $appointments = [];
            $appQuery = UserAppointment::where('id_society', $society->id)
                ->whereBetween('ap_datetime',[
                    date('y-m-d').'00:00:00',
                    date('y-m-d', strtotime('+20 days')).'23:59:59'
                ])
                ->get();
            foreach($appQuery as $appItem){
                $appointments[] = $appItem['ap_datetime'];
            }
            //disponibilidade real
            for($q=0;$q<20;$q++){
                $timeItem = strtotime('+'.$q.'days');
                $weekday = date('w',$timeItem);

                if (in_array($weekday, array_keys($availWeekdays))) {
                  $hours = [];

                  $dayItem = date('y-m-d', $timeItem);

                  foreach($availWeekdays[$weekday] as $hourItem){
                    $dayFormated = $dayItem.''.$hourItem.':00';
                    if(in_array($dayFormated, $appointments)){
                        $hours[]=$hourItem;
                    }
                  }
                  if(count($hours)>0){
                      $availability[]=[
                          'date'=> $dayItem,
                          'hour'=> $hourItem
                      ];
                  }
                }
            }


            $society['available'] = $availability;

        }else {
            $array['error']= 'Barbeiro não encontrado';
            return $array;
        }

        return $array;
    }

    public function setAppointment($id, Request $request){
        // service, year, month, day, hour

        $array = ['error'=>''];

        $service = $request->input('service');
        $year =  intval($request->input('year'));
        $month =  intval($request->input('month'));
        $day =  intval($request->input('day'));
        $hour =  intval($request->input('hour'));

        //validação month day hour
        $month = ($month <10) ? '0'.$month : $month;
        $day = ($day <10) ? '0'.$day : $day;
        $hour = ($hour <10) ? '0'.$hour : $hour;

        //verificações
        //1. verificar se o barbeiro existe
        $societyservice = SocietyServices::select()
        ->where('id',$service)
        ->where('id_society',$id)
        ->first();

        if ($societyservice) {
            //2. verificar se a data e real
            $apDate = $year.'-'.$month.'-'.$day.' '.$hour.':00:00';
            if (strtotime($apDate)>0) {
               //3. verificar se ja possui agendamento nesta data
               $apps = UserAppointment::select()
                ->where('id_society',$id)
                ->where('ap_datetime',$apDate)
            ->count();
            if ($apps === 0 ) {
                //4. verificar se o barbeiro atende nesta data
                    // 4.1 verificando o dia da semana disponivel
                $weekday = date('w', strtotime($apDate));
                $avail = SocietyAvailability::select()
                    ->where('id_society', $id)
                    ->where('weekday', $weekday)
                ->first();
                if($avail){
                    //4.2 verifica se o barbeiro atende nessa hora
                    $hours = explode(',', $avail['hours']);
                    if (in_array($hour.':00',$hours)) {
                        //5. fazer o agendamento
                        $newApp = new UserAppointment();
                        $newApp->id_user = $this->loggedUser->id;
                        $newApp->id_society = $id;
                        $newApp->id_service = $service;
                        $newApp->ap_datetime = $apDate;
                        $newApp->save();
                    }else {
                        $array['error'] = 'hora indisponível';
                    }
                }else {
                    $array['error'] =  'Não disponivel neste dia';
                }
            }else {
                $array['error'] = 'Já possui  agendamento neste dia';
            }
            }else {
                $array['error'] = 'Data inválida';
            }
        }else{
            $array['error'] = 'serviço inexistente';
        }
        return $array;
    }

    public function search(Request $request){
        $array = ['error' =>'','list'=>[]];

        $q = $request->input('q');

        if ($q) {

            $societys = Society::select()
                ->where('name','LIKE','%'.$q.'%')
            ->get();

            foreach ($societys as $bkey => $society){
                $societys[$bkey]['avatar'] = url('media/avatars/'.$societys[$bkey]['avatar']);
            }
            $array['list'] = $societys;
        }else {
            $array['error'] = 'digite algo para busca';
        }
        return $array;
    }

}
