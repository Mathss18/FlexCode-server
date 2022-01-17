<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\PrivateMessageSent;
use App\Helpers\APIHelper;
use App\Models\Message;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function fetchMessages()
    {
        try {
            $messages = Message::with('usuario')->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $messages);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function fetchPrivateMessages($id)
    {
        try {

            $messages = Message::with('usuario')
                ->where(['usuario_id' => auth()->user()->id, 'usuario_receptor_id' => $id]) // de mim para ela
                ->orWhere(function ($query) use ($id) {
                    $query->where('usuario_id', $id)->where('usuario_receptor_id', auth()->user()->id); // de ela para mim
                })
                ->get();

            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $messages);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function sendMessage(Request $request)
    {
        try {
            $message = auth()->user()->messages()->create(['message' => $request->message]);
            broadcast(new MessageSent(auth()->user(), $message))->toOthers();
            return response(['status' => 'Message sent successfully.'], 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function sendPrivateMessage(Request $request)
    {
        try {
            $message = auth()->user()->messages()->create($request->all());
            $usuario = Usuario::findOrFail($message->usuario_id);
            $message->usuario = $usuario;
            broadcast(new PrivateMessageSent($message))->toOthers();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso', $message);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function getUnreadMessages()
    {
        try {
            $messages = Message::where('vizualizado', false)->get();
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao trazer as mensagens não lidas', $messages);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }

    public function readMessages(Request $request)
    {

        try {
            $messages = Message::where('usuario_id', $request->usuario_id)->where('vizualizado', false)->update(['vizualizado' => true]);
            $response = APIHelper::APIResponse(true, 200, 'Sucesso ao ler as mensagens', $messages);
            return response()->json($response, 200);
        } catch (Exception  $ex) {
            $response = APIHelper::APIResponse(false, 500, null, null, $ex);
            return response()->json($response, 500);
        }
    }
}
