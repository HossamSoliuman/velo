<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnquiryRequest;
use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EnquiryController extends Controller
{
    public const SENT_MESSAGE = 'Thank you! We have received your enquiry and will get back to you shortly.';

    /**
     * Save an enquiry from the product modal or the contact page, and email it to the business.
     * The modal posts in the background and receives JSON; the plain form is redirected back.
     */
    public function store(StoreEnquiryRequest $request): JsonResponse|RedirectResponse
    {
        if (! $request->isSpam()) {
            $enquiry = new Enquiry($request->safe()->only(['name', 'company', 'email', 'mobile', 'quantity', 'message']));

            if ($product = $request->product()) {
                $enquiry->attachProduct($product);
            }

            $enquiry->save();

            $this->notifyBusiness($enquiry);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => self::SENT_MESSAGE], 201);
        }

        return redirect()->to(route('contact').'#enquiry')->with('enquiry_sent', self::SENT_MESSAGE);
    }

    private function notifyBusiness(Enquiry $enquiry): void
    {
        $recipient = SiteSetting::value('enquiry_email');

        if (blank($recipient)) {
            Log::warning('Enquiry email not sent because no enquiry email address is set.', ['enquiry_id' => $enquiry->id]);

            return;
        }

        Mail::to($recipient)->send(new EnquiryReceived($enquiry));
    }
}
