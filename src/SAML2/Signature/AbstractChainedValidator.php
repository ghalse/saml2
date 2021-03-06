<?php

declare(strict_types=1);

namespace SAML2\Signature;

use Exception;
use Psr\Log\LoggerInterface;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Utils;
use SAML2\XML\SignedElementInterface;
use SAML2\Utilities\ArrayCollection;

abstract class AbstractChainedValidator implements ChainedValidator
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;


    /**
     * Constructor for AbstractChainedValidator
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * BC compatible version of the signature check
     *
     * @param \SAML2\XML\SignedElementInterface    $element
     * @param \SAML2\Utilities\ArrayCollection $pemCandidates
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function validateElementWithKeys(
        SignedElementInterface $element,
        ArrayCollection $pemCandidates
    ): bool {
        $lastException = null;
        foreach ($pemCandidates as $index => $candidateKey) {
            $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
            $key->loadKey($candidateKey->getCertificate());
            $key = Utils::castKey($key, $element->getSignature()->getAlgorithm(), 'public');

            try {
                /*
                 * Make sure that we have a valid signature on either the response or the assertion.
                 */
                $result = $element->validate($key);
                if ($result) {
                    $this->logger->debug(sprintf('Validation with key "#%d" succeeded', $index));
                    return true;
                }
                $this->logger->debug(sprintf('Validation with key "#%d" failed without exception.', $index));
            } catch (Exception $e) {
                $this->logger->debug(sprintf(
                    'Validation with key "#%d" failed with exception: %s',
                    $index,
                    $e->getMessage()
                ));

                $lastException = $e;
            }
        }

        if ($lastException !== null) {
            throw $lastException;
        } else {
            return false;
        }
    }
}
