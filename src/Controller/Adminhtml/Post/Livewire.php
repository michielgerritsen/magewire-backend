<?php declare(strict_types=1);

namespace Magewirephp\MagewireBackend\Controller\Adminhtml\Post;

use Exception;
use Laminas\Http\AbstractMessage;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\State;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magewirephp\Magewire\Component;
use Magewirephp\Magewire\Exception\LifecycleException;
use Magewirephp\Magewire\Helper\Security as SecurityHelper;
use Magewirephp\Magewire\ViewModel\Magewire as MagewireViewModel;
use Magewirephp\Magewire\Model\RequestInterface as MagewireRequestInterface;
use Magewirephp\Magewire\Model\ComponentResolver;
use Magewirephp\Magewire\Model\HttpFactory;
use Magewirephp\MagewireBackend\Exception\InvalidHttpMethodException;
use Magewirephp\MagewireBackend\Exception\InvalidHttpParameterException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Livewire extends Action
{
    const ADMIN_RESOURCE = 'Magewirephp_MagewireBackend::admin';

    protected HttpFactory $httpFactory;
    protected JsonFactory $resultJsonFactory;
    protected SecurityHelper $securityHelper;
    protected LoggerInterface $logger;
    protected MagewireViewModel $magewireViewModel;
    protected ComponentResolver $componentResolver;
    protected SerializerInterface $serializer;
    protected State $appState;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        SerializerInterface $serializer,
        HttpFactory $httpFactory,
        SecurityHelper $securityHelper,
        LoggerInterface $logger,
        MagewireViewModel $magewireViewModel,
        ComponentResolver $componentResolver,
        State $appState
    ) {
        parent::__construct($context);
        $this->httpFactory = $httpFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->securityHelper = $securityHelper;
        $this->logger = $logger;
        $this->magewireViewModel = $magewireViewModel;
        $this->componentResolver = $componentResolver;
        $this->serializer = $serializer;
        $this->appState = $appState;
    }

    public function execute(): Json
    {
        try {
            if (false === $this->getRequest()->isPost()) {
                throw new InvalidHttpMethodException('Only POST requests are allowed');
            }

            try {
                $request = $this->httpFactory->createRequest($this->getRequestParams())->isSubsequent(true);
            } catch (LocalizedException $exception) {
                throw new HttpException(400);
            }

            $component = $this->locateWireComponent($request);
            $component->setRequest($request);

            $html = $component->getParent()->toHtml();
            $response = $component->getResponse();

            if ($response === null) {
                throw new LifecycleException(__('Response object not found for component'));
            }

            // Set final HTML for response.
            $response->effects['html'] = $html;
            // Prepare result object.
            $result = $this->resultJsonFactory->create();

            return $result->setData([
                'effects' => $response->getEffects(),
                'serverMemo' => $response->getServerMemo()
            ]);
        } catch (Exception $exception) {
            return $this->handleException($exception);
        }
    }

    public function _processUrlKeys()
    {
        return true;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function locateWireComponent(MagewireRequestInterface $request): Component
    {
        $resolverFingerprint = $request->getFingerprint('resolver');
        if (empty($resolverFingerprint)) {
            throw new InvalidHttpParameterException('Empty HTTP parameter "fingerprint.resolver"');
        }

        $resolver = $this->componentResolver->get($resolverFingerprint);
        return $resolver->reconstruct($request)->setResolver($resolver);
    }

    public function handleException(Exception $exception): Json
    {
        if ($this->isDeveloperMode()) {
            throw $exception;
        }

        $result = $this->resultJsonFactory->create();
        $statuses = $this->getHttpResponseStatuses();

        $code = $exception instanceof HttpException ? $exception->getStatusCode() : $exception->getCode();
        $message = empty($exception->getMessage()) ? ($statuses[$code] ?? 'Something went wrong') : $exception->getMessage();

        // Make an exception for optional outsiders.
        $code = in_array($code, [0, -1], true) ? Response::HTTP_INTERNAL_SERVER_ERROR : $code;
        // Try and grep the status from the available stack or get 500 when it's unavailable.
        $code = $statuses[$code] ? $code : Response::HTTP_INTERNAL_SERVER_ERROR;
        // Set the status header with the returned code and belonging response phrase.
        $result->setStatusHeader($code, AbstractMessage::VERSION_11, $statuses[$code]);

        if ($code === 500) {
            $this->logger->critical($exception->getMessage());
        }

        return $result->setData([
            'message' => $message,
            'code' => $code
        ]);
    }

    /**
     * @return array
     */
    public function getHttpResponseStatuses(): array
    {
        $statuses = Response::$statusTexts;
        $statuses[419] = 'Session expired';

        return $statuses;
    }

    /**
     * @return array
     */
    private function getRequestParams(): array
    {
        $params = $this->_request->getParams();
        $content = $this->_request->getContent();
        if (!empty($content)) {
            $params = $this->serializer->unserialize($content);
        }

        unset($params['form_key']);
        return $params;
    }

    /**
     * @return bool
     */
    private function isDeveloperMode(): bool
    {
        return $this->appState->getMode() === State::MODE_DEVELOPER;
    }
}
