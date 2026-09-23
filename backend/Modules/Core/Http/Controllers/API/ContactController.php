<?php

namespace Modules\Core\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Rules\NoScriptTag;

use Validator;
use Illuminate\Support\Facades\Mail;

use Modules\Core\Emails\ContactFormMail;
use Modules\Core\Rules\PhoneNumber;
use Modules\Core\Rules\NoSpecialCharacter;

class ContactController extends Controller
{
    public function submitForm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => [
                'required',
                'string',
                'max:250',
                new NoScriptTag,
                new NoSpecialCharacter('name')
            ],
            'contact_no' => [
                'required',
                'digits:10',
                new PhoneNumber(),
            ],
            'email' => 'required|email|max:255',
            'message' => [
                'required',
                'string',
                'max:1000',
                new NoScriptTag,
                new NoSpecialCharacter('message'),
            ],
        ], [
            'full_name.required' => __('contact.required', ['attribute' => __('contact.attributes.full_name')]),
            'full_name.string' => __('contact.string', ['attribute' => __('contact.attributes.full_name')]),
            'full_name.max' => __('contact.max', ['attribute' => __('contact.attributes.full_name'), 'max' => 255]),
            'contact_no.required' => __('contact.required', ['attribute' => __('contact.attributes.contact_no')]),
            'contact_no.digits' => __('contact.digits', ['attribute' => __('contact.attributes.contact_no'), 'digits' => 10]),
            'email.required' => __('contact.required', ['attribute' => __('contact.attributes.email')]),
            'email.email' => __('contact.email', ['attribute' => __('contact.attributes.email')]),
            'email.max' => __('contact.max', ['attribute' => __('contact.attributes.email'), 'max' => 255]),
            'message.required' => __('contact.required', ['attribute' => __('contact.attributes.message')]),
            'message.string' => __('contact.string', ['attribute' => __('contact.attributes.message')]),
            'message.max' => __('contact.max', ['attribute' => __('contact.attributes.message'), 'max' => 1000]),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Optional: Save data to the database
        $formData = $request->only(['full_name', 'contact_no', 'email', 'message']);
        // Contact::create($formData); // Uncomment if saving to database

        // Send email
        Mail::to(env('ROOT_ADMIN_EMAIL_ADDRESS'))->send(new ContactFormMail($formData));

        return response()->json([
            'status' => 'success',
            'message' => __('contact.success_message'),
        ]);
    }
}
