<?php

namespace App\Services;

/**
 * Génère le XML Factur-X (profil MINIMUM / EN16931) conforme ZUGFeRD 2.3
 * Obligation France 2026 — art. L.153-22 Code Général des Impôts.
 *
 * Profil : EN 16931 (COMFORT dans la nomenclature ZUGFeRD)
 * Namespace : urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100
 */
class FacturX
{
    public static function generate(array $invoice, array $lines, array $company, array $client): string
    {
        $fmt4    = fn(float $n) => number_format($n, 4, '.', '');
        $fmt2    = fn(float $n) => number_format($n, 2, '.', '');
        $date    = fn(string $d) => str_replace('-', '', substr($d, 0, 10)); // YYYYMMDD
        $esc     = fn(string $s) => htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $issueDate  = $date($invoice['issue_date']);
        $dueDate    = $date($invoice['due_date']);
        $currency   = strtoupper($invoice['currency'] ?? 'EUR');
        $vatCountry = strtoupper($invoice['country_vat'] ?? 'FR');

        // Regrouper les lignes par taux de TVA pour le bloc taxe
        $vatGroups = [];
        foreach ($lines as $line) {
            $rate = (float)($line['vat_rate'] ?? 20.00);
            $key  = number_format($rate, 2, '.', '');
            if (!isset($vatGroups[$key])) {
                $vatGroups[$key] = ['basis' => 0.0, 'amount' => 0.0, 'rate' => $rate];
            }
            $vatGroups[$key]['basis']  += (float)$line['total_ht'];
            $vatGroups[$key]['amount'] += (float)$line['total_ttc'] - (float)$line['total_ht'];
        }

        $totalHt       = (float)$invoice['subtotal_ht'];
        $totalDiscount = (float)($invoice['total_discount'] ?? 0);
        $totalVat      = (float)$invoice['total_vat'];
        $totalTtc      = (float)$invoice['total_ttc'];
        $amountPaid    = (float)($invoice['amount_paid'] ?? 0);
        $dueAmount     = $totalTtc - $amountPaid;

        // Lignes de facture
        $lineItems = '';
        foreach ($lines as $pos => $line) {
            $lineNo   = $pos + 1;
            $lineHt   = (float)$line['total_ht'];
            $lineQty  = (float)($line['quantity'] ?? 1);
            $lineUp   = (float)$line['unit_price'];
            $lineVat  = (float)($line['vat_rate'] ?? 20.00);
            $lineDisc = (float)($line['discount_value'] ?? 0);
            $discType = $line['discount_type'] ?? null;

            $discXml = '';
            if ($lineDisc > 0 && $discType) {
                $discAmt = $discType === 'percent'
                    ? $lineUp * $lineQty * $lineDisc / 100
                    : $lineDisc;
                $discXml = '<ram:SpecifiedTradeAllowanceCharge>
                    <ram:ChargeIndicator><udt:Indicator>false</udt:Indicator></ram:ChargeIndicator>
                    <ram:ActualAmount>' . $fmt4($discAmt) . '</ram:ActualAmount>
                </ram:SpecifiedTradeAllowanceCharge>';
            }

            $lineItems .= <<<XML
    <rsm:IncludedSupplyChainTradeLineItem>
        <ram:AssociatedDocumentLineDocument>
            <ram:LineID>{$lineNo}</ram:LineID>
            <ram:IncludedNote><ram:Content>{$esc((string)($line['description'] ?? ''))}</ram:Content></ram:IncludedNote>
        </ram:AssociatedDocumentLineDocument>
        <ram:SpecifiedTradeProduct>
            <ram:Name>{$esc($line['name'])}</ram:Name>
            {$esc((string)($line['reference'] ?? '')) !== '' ? '<ram:SellerAssignedID>' . $esc((string)$line['reference']) . '</ram:SellerAssignedID>' : ''}
        </ram:SpecifiedTradeProduct>
        <ram:SpecifiedLineTradeAgreement>
            <ram:NetPriceProductTradePrice>
                <ram:ChargeAmount>{$fmt4($lineUp)}</ram:ChargeAmount>
            </ram:NetPriceProductTradePrice>
        </ram:SpecifiedLineTradeAgreement>
        <ram:SpecifiedLineTradeDelivery>
            <ram:BilledQuantity unitCode="{$esc($line['unit'] ?? 'C62')}">{$fmt4($lineQty)}</ram:BilledQuantity>
        </ram:SpecifiedLineTradeDelivery>
        <ram:SpecifiedLineTradeSettlement>
            <ram:ApplicableTradeTax>
                <ram:TypeCode>VAT</ram:TypeCode>
                <ram:CategoryCode>S</ram:CategoryCode>
                <ram:RateApplicablePercent>{$fmt2($lineVat)}</ram:RateApplicablePercent>
            </ram:ApplicableTradeTax>
            {$discXml}
            <ram:SpecifiedTradeSettlementLineMonetarySummation>
                <ram:LineTotalAmount>{$fmt4($lineHt)}</ram:LineTotalAmount>
            </ram:SpecifiedTradeSettlementLineMonetarySummation>
        </ram:SpecifiedLineTradeSettlement>
    </rsm:IncludedSupplyChainTradeLineItem>
XML;
        }

        // Bloc taxes
        $taxBlock = '';
        foreach ($vatGroups as $vg) {
            $taxBlock .= <<<XML
    <ram:ApplicableTradeTax>
        <ram:CalculatedAmount>{$fmt2($vg['amount'])}</ram:CalculatedAmount>
        <ram:TypeCode>VAT</ram:TypeCode>
        <ram:BasisAmount>{$fmt2($vg['basis'])}</ram:BasisAmount>
        <ram:CategoryCode>S</ram:CategoryCode>
        <ram:RateApplicablePercent>{$fmt2($vg['rate'])}</ram:RateApplicablePercent>
    </ram:ApplicableTradeTax>
XML;
        }

        $companyVat = $esc($company['vat_number'] ?? '');
        $clientVat  = $esc($client['vat_number'] ?? '');

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rsm:CrossIndustryInvoice
    xmlns:rsm="urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100"
    xmlns:ram="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100"
    xmlns:udt="urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100"
    xmlns:qdt="urn:un:unece:uncefact:data:standard:QualifiedDataType:100">

  <rsm:ExchangedDocumentContext>
    <ram:GuidelineSpecifiedDocumentContextParameter>
      <ram:ID>urn:cen.eu:en16931:2017#compliant#urn:factur-x.eu:1p0:en16931</ram:ID>
    </ram:GuidelineSpecifiedDocumentContextParameter>
  </rsm:ExchangedDocumentContext>

  <rsm:ExchangedDocument>
    <ram:ID>{$esc($invoice['number'])}</ram:ID>
    <ram:TypeCode>380</ram:TypeCode>
    <ram:IssueDateTime>
      <udt:DateTimeString format="102">{$issueDate}</udt:DateTimeString>
    </ram:IssueDateTime>
  </rsm:ExchangedDocument>

  <rsm:SupplyChainTradeTransaction>

    {$lineItems}

    <ram:ApplicableHeaderTradeAgreement>
      <ram:SellerTradeParty>
        <ram:Name>{$esc($company['name'])}</ram:Name>
        <ram:SpecifiedLegalOrganization>
          <ram:ID schemeID="0002">{$esc(preg_replace('/\s/', '', $company['siret'] ?? ''))}</ram:ID>
        </ram:SpecifiedLegalOrganization>
        <ram:PostalTradeAddress>
          <ram:CountryID>{$esc($company['country'] ?? 'FR')}</ram:CountryID>
          <ram:LineOne>{$esc($company['address'] ?? '')}</ram:LineOne>
          <ram:PostcodeCode>{$esc($company['zip'] ?? '')}</ram:PostcodeCode>
          <ram:CityName>{$esc($company['city'] ?? '')}</ram:CityName>
        </ram:PostalTradeAddress>
        {$companyVat !== '' ? '<ram:SpecifiedTaxRegistration><ram:ID schemeID="VA">' . $companyVat . '</ram:ID></ram:SpecifiedTaxRegistration>' : ''}
      </ram:SellerTradeParty>
      <ram:BuyerTradeParty>
        <ram:Name>{$esc($client['name'])}</ram:Name>
        <ram:PostalTradeAddress>
          <ram:CountryID>{$esc($client['country'] ?? 'FR')}</ram:CountryID>
          <ram:LineOne>{$esc($client['address'] ?? '')}</ram:LineOne>
          <ram:PostcodeCode>{$esc($client['zip'] ?? '')}</ram:PostcodeCode>
          <ram:CityName>{$esc($client['city'] ?? '')}</ram:CityName>
        </ram:PostalTradeAddress>
        {$clientVat !== '' ? '<ram:SpecifiedTaxRegistration><ram:ID schemeID="VA">' . $clientVat . '</ram:ID></ram:SpecifiedTaxRegistration>' : ''}
      </ram:BuyerTradeParty>
    </ram:ApplicableHeaderTradeAgreement>

    <ram:ApplicableHeaderTradeDelivery/>

    <ram:ApplicableHeaderTradeSettlement>
      <ram:InvoiceCurrencyCode>{$currency}</ram:InvoiceCurrencyCode>
      {$taxBlock}
      <ram:SpecifiedTradePaymentTerms>
        <ram:DueDateDateTime>
          <udt:DateTimeString format="102">{$dueDate}</udt:DateTimeString>
        </ram:DueDateDateTime>
      </ram:SpecifiedTradePaymentTerms>
      <ram:SpecifiedTradeSettlementHeaderMonetarySummation>
        <ram:LineTotalAmount>{$fmt2($totalHt)}</ram:LineTotalAmount>
        <ram:AllowanceTotalAmount>{$fmt2($totalDiscount)}</ram:AllowanceTotalAmount>
        <ram:TaxBasisTotalAmount>{$fmt2($totalHt - $totalDiscount)}</ram:TaxBasisTotalAmount>
        <ram:TaxTotalAmount currencyID="{$currency}">{$fmt2($totalVat)}</ram:TaxTotalAmount>
        <ram:GrandTotalAmount>{$fmt2($totalTtc)}</ram:GrandTotalAmount>
        <ram:TotalPrepaidAmount>{$fmt2($amountPaid)}</ram:TotalPrepaidAmount>
        <ram:DuePayableAmount>{$fmt2($dueAmount)}</ram:DuePayableAmount>
      </ram:SpecifiedTradeSettlementHeaderMonetarySummation>
    </ram:ApplicableHeaderTradeSettlement>

  </rsm:SupplyChainTradeTransaction>
</rsm:CrossIndustryInvoice>
XML;

        return $xml;
    }

    /**
     * Valide (basiquement) que le XML généré est bien formé.
     */
    public static function validate(string $xml): bool
    {
        libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $ok  = $doc->loadXML($xml);
        libxml_use_internal_errors(false);
        return $ok !== false;
    }

    /**
     * Stocke le XML Factur-X sur la facture en base.
     */
    public static function saveToInvoice(int $invoiceId, string $xml): void
    {
        \App\Helpers\Database::query(
            'UPDATE invoices SET facturx_xml = ?, updated_at = NOW() WHERE id = ?',
            [$xml, $invoiceId]
        );
    }

    /**
     * Retourne le XML stocké ou le génère à la volée.
     */
    public static function getOrGenerate(array $invoice, array $lines, array $company, array $client): string
    {
        if (!empty($invoice['facturx_xml'])) {
            return $invoice['facturx_xml'];
        }
        $xml = self::generate($invoice, $lines, $company, $client);
        self::saveToInvoice((int)$invoice['id'], $xml);
        return $xml;
    }
}
