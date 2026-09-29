<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PasscodeAuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Check if session has authorized flag
        if (!$session->get('is_authorized')) {
            // If AJAX or JSON request, return 401 JSON response
            if ($request->isAJAX() || (method_exists($request, 'hasHeader') && $request->hasHeader('Accept') && str_contains($request->getHeaderLine('Accept'), 'application/json'))) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON([
                        'success'  => false,
                        'message'  => 'Sesi akses Anda telah berakhir atau belum terautentikasi.',
                        'redirect' => site_url('login'),
                    ]);
            }

            // Normal web page: save intended URL and redirect to login
            $currentUrl = current_url();
            if (!str_contains($currentUrl, 'login') && !str_contains($currentUrl, 'logout')) {
                $session->set('auth_redirect_url', $currentUrl);
            }

            return redirect()->to(site_url('login'))->with('auth_error', 'Silakan masukkan passcode akses kantor untuk membuka sistem.');
        }
    }

    /**
     * We don't have anything to do here.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing needed
    }
}
