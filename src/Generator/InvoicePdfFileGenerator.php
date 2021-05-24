<?php

declare(strict_types=1);

namespace Sylius\InvoicingPlugin\Generator;

use Qipsius\TCPDFBundle\Controller\TCPDFController;

use Sylius\InvoicingPlugin\Entity\InvoiceInterface;
use Sylius\InvoicingPlugin\Model\InvoicePdf;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Config\FileLocatorInterface;

final class InvoicePdfFileGenerator implements InvoicePdfFileGeneratorInterface
{
    private const FILE_EXTENSION = '.pdf';

    /** @var EngineInterface */
    private $templatingEngine;

    /** @var TCPDFController */
    private $tcpdf;

    /** @var FileLocatorInterface */
    private $fileLocator;

    /** @var string */
    private $template;

    /** @var string */
    private $invoiceLogoPath;

    public function __construct(
        EngineInterface $templatingEngine,
        TCPDFController $tcpdf,
        FileLocatorInterface $fileLocator,
        string $template,
        string $invoiceLogoPath
    ) {
        $this->templatingEngine = $templatingEngine;
        $this->tcpdf = $tcpdf;
        $this->fileLocator = $fileLocator;
        $this->template = $template;
        $this->invoiceLogoPath = $invoiceLogoPath;
    }

    public function generate(InvoiceInterface $invoice): InvoicePdf
    {
        /** @var string $filename */
        $filename = str_replace('/', '_', $invoice->number()) . self::FILE_EXTENSION;

        $html = $this->templatingEngine->render($this->template, [
                'invoice' => $invoice,
                'channel' => $invoice->channel(),
                'invoiceLogoPath' => $this->fileLocator->locate($this->invoiceLogoPath),
            ]);

        $this->tcpdf->AddPage();
        $this->tcpdf->writeHTML($html, true, false, true, false, '');
        $pdf = $pdf->Output($filename, 'F');

        return new InvoicePdf($filename, $pdf);
    }
}
