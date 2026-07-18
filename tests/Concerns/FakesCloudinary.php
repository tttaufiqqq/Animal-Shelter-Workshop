<?php

namespace Tests\Concerns;

use Cloudinary\Api\ApiResponse;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Cloudinary;
use Mockery;

/**
 * cloudinary()->uploadApi()->upload()/destroy() call the real Cloudinary SDK
 * directly (not Laravel's Storage facade), so Storage::fake() does not
 * intercept it — .env.testing leaves CLOUDINARY_URL empty, so an unfaked
 * call would either throw a config error or hit the real API. Binding a
 * Mockery double over the Cloudinary::class singleton keeps upload/destroy
 * calls in-process and deterministic.
 */
trait FakesCloudinary
{
    protected function fakeCloudinary(): void
    {
        $uploadApi = Mockery::mock(UploadApi::class);
        $uploadApi->shouldReceive('upload')->andReturnUsing(
            fn($path, $options = []) => new ApiResponse(['public_id' => $options['public_id'] ?? 'fake/' . uniqid()], [])
        );
        $uploadApi->shouldReceive('destroy')->andReturnUsing(
            fn() => new ApiResponse(['result' => 'ok'], [])
        );

        $cloudinary = Mockery::mock(Cloudinary::class);
        $cloudinary->shouldReceive('uploadApi')->andReturn($uploadApi);

        $this->app->instance(Cloudinary::class, $cloudinary);
    }
}
