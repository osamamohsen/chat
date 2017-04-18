<?php

namespace App\Http\Controllers;

use App\Message;
use Illuminate\Http\Request;


class chatController extends Controller
{
    public function __construct()

    {
        $this->middleware('auth');

    }

    /**
     * save user message
     * @param Request $request
     * @return array
     */
    public function sendMessage(Request $request){

        $user =  \Auth::user();
        $user->messages()->create([
            'message' => $request->get('message'),
            'user_to' => $request->get('user_to')
        ]);

        return ['status' => 'OK'];
    }


    /**
     * get All messages from users
     * @param Request $request
     * @return array
     */
    public function getMessage(Request $request){
        if($request->get('user_to')==0){
            $messages = Message::all();
        }else{
            $messages = Message::where('user_id',\Auth::user()->id)
                    ->where('user_to',$request->get('user_to'))
                    ->orwhere('user_id',$request->get('user_to'))
                    ->where('user_to',\Auth::user()->id)
                    ->get();
        }
        return ['messages' => $messages];
    }
}
