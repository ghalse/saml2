<?php

namespace SAML2\XML\shibmd;

use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\shibmd\Scope
 */
class ScopeTest extends \PHPUnit_Framework_TestCase
{
    public function testMarshalling()
    {
        $scope = new Scope();
        $scope->scope = "example.org";
        $scope->regexp = FALSE;

        $document = DOMDocumentFactory::fromString('<root />');
        $scopeElement = $scope->toXML($document->firstChild);

        $scopeElements = Utils::xpQuery($scopeElement, '/root/shibmd:Scope');
        $this->assertCount(1, $scopeElements);
        $scopeElement = $scopeElements[0];

        $this->assertEquals('example.org', $scopeElement->nodeValue);
        $this->assertEquals('urn:mace:shibboleth:metadata:1.0', $scopeElement->namespaceURI);
        $this->assertEquals('false', $scopeElement->getAttribute('regexp'));

        $scope = new Scope();
        $scope->scope = "^(.*\.)?example\.edu$";
        $scope->regexp = TRUE;

        $document = DOMDocumentFactory::fromString('<root />');
        $scopeElement = $scope->toXML($document->firstChild);

        $scopeElements = Utils::xpQuery($scopeElement, '/root/shibmd:Scope');
        $this->assertCount(1, $scopeElements);
        $scopeElement = $scopeElements[0];

        $this->assertEquals('^(.*\.)?example\.edu$', $scopeElement->nodeValue);
        $this->assertEquals('urn:mace:shibboleth:metadata:1.0', $scopeElement->namespaceURI);
        $this->assertEquals('true', $scopeElement->getAttribute('regexp'));
    }

    public function testUnmarshalling()
    {
        $document = DOMDocumentFactory::fromString(
<<<XML
<shibmd:Scope regexp="false">example.org</shibmd:Scope>
XML
        );
        $scope = new Scope($document->firstChild);

        $this->assertEquals('example.org', $scope->scope);
        $this->assertFalse($scope->regexp);

        $document = DOMDocumentFactory::fromString(
<<<XML
<shibmd:Scope regexp="true">^(.*|)example.edu$</shibmd:Scope>
XML
        );
        $scope = new Scope($document->firstChild);

        $this->assertEquals('^(.*|)example.edu$', $scope->scope);
        $this->assertTrue($scope->regexp);
    }
}