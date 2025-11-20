<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;

class Handler extends ExceptionHandler
{
    /**
     * Campos que nunca se guardan en sesión tras errores de validación.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Registra callbacks personalizados para el manejo de excepciones.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
        });
    }

    public function render($request, Throwable $e)
    {
        $status = $this->resolveStatusCode($e);

        if($status && $this->shouldRenderCustomSectionView($request, $status)) {
            return $this->renderSectionErrorView($request, $status, $e);
        }

        return parent::render($request, $e);
    }

    protected function shouldRenderCustomSectionView(Request $request, int $status): bool
    {
        if(!in_array($status, [403, 404], true)) {
            return false;
        }

        return $this->resolveSectionView($request) !== null;
    }

    protected function renderSectionErrorView(Request $request, int $status, Throwable $e)
    {
        $view = $this->resolveSectionView($request);
        if(!$view) {
            return parent::render($request, $e);
        }

        $headers = $e instanceof HttpExceptionInterface ? $e->getHeaders() : [];
        $errorsBag = session('errors');
        if(!$errorsBag instanceof ViewErrorBag) {
            $errorsBag = new ViewErrorBag();
        }
        app('view')->share('errors', $errorsBag);

        return response()->view($view, [], $status, $headers);
    }

    protected function resolveSectionView(Request $request): ?string
    {
        $segment = $request->segment(1) ?? '';

        if(Str::startsWith($segment, ['friends', 'users'])) {
            return 'errors.friends';
        }

        if(Str::startsWith($segment, 'posts')) {
            return 'errors.posts';
        }

        if(Str::startsWith($segment, 'games')) {
            return 'errors.games';
        }

        if(Str::startsWith($segment, 'groups')) {
            return 'errors.groups';
        }

        return null;
    }

    protected function resolveStatusCode(Throwable $e): ?int
    {
        if($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            return $status === 405 ? 404 : $status;
        }

        if($e instanceof ModelNotFoundException) {
            return 404;
        }

        if($e instanceof AuthorizationException) {
            return 403;
        }

        return null;
    }
}
