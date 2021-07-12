<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        return response(User::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'cpf' => 'required|string|size:14',
            'email' => 'required|email',
            'isShopkeeper' => 'nullable|boolean',
            'balance' => 'nullable|numeric',
            'password' => 'required',
        ]);

        try {
            $request->password = bcrypt($request->password);
            $user = User::create($request->all());
        } catch (Exception $exception) {
            return response([
                'error' => $exception->getMessage(),
                500
            ]);
        }

        return response("User $user->name was successfully created.");
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show(int $id): Response
    {
        return response(User::find($id));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy(int $id): Response
    {
        try {
            $user = User::find($id);
            $user->delete();

            return response("User $user->name was successfully deleted.");
        } catch (Exception $exception) {
            return response(
                ['error' => $exception->getMessage()],
                500
            );
        }
    }
}
