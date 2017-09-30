<?php

namespace App\Http\Requests;

use App\Invite;
use App\Tournament;
use Illuminate\Support\Facades\Auth;

class InviteRequest extends Request
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'recipients' => 'required',
        ];
    }
}
