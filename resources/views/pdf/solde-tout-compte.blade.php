<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solde de tout compte - {{ $resident->NOMRESIDENT }} {{ $resident->PRENOMRESIDENT ?? '' }}</title>
    
</head>
<body>
    <div class="header">
        <h1>Solde de Tout Compte</h1>
        <div class="subtitle">Document généré le {{ $dateGeneration }} à {{ $heureGeneration }}</div>
    </div>

    <div class="info-section">
        <h2>Informations du Résident</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nom :</div>
                <div class="info-value">{{ $resident->NOMRESIDENT }} {{ $resident->PRENOMRESIDENT ?? '' }}</div>
            </div>
            @if($resident->chambre)
            <div class="info-row">
                <div class="info-label">Numéro de chambre :</div>
                <div class="info-value">{{ $resident->chambre->IDBATIMENT }}{{ $resident->chambre->NUMEROCHAMBRE }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Date d'entrée :</div>
                <div class="info-value">{{ $resident->DATEENTREE ? \Carbon\Carbon::parse($resident->DATEENTREE)->format('d/m/Y') : ($resident->DATEINSCRIPTION ? \Carbon\Carbon::parse($resident->DATEINSCRIPTION)->format('d/m/Y') : '_______________') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date de sortie :</div>
                <div class="info-value">{{ $resident->DATEDEPART ? \Carbon\Carbon::parse($resident->DATEDEPART)->format('d/m/Y') : '_______________' }}</div>
            </div>
        </div>
    </div>

    <div class="inspection-section">
        <h2>État des Lieux de Sortie</h2>
        <div class="inspection-grid">
            <div class="inspection-row">
                <div class="inspection-label">Date de l'état des lieux :{{ $resident->DATEDEPART ? \Carbon\Carbon::parse($resident->DATEDEPART)->format('d/m/Y') : '_______________' }} </div>
                
            </div>
        </div>
        
        <h3 style="color: #20364B; margin: 20px 0 10px 0;">Présents :</h3>
        <div class="inspection-content">
            <div class="inspection-item">• Représentant de la résidence : M DENIS - DIRECTEUR</div>
            <div class="inspection-item">• Résident</div>
            <div class="inspection-item">• Autres participants :</div>
        </div>
        
        <h3 style="color: #20364B; margin: 20px 0 10px 0;">Description et État de la chambre/appartement à la sortie :</h3>
        <div class="inspection-content">
            <div class="inspection-item">Tout est en ordre, aucun problème à signaler.</div>
        </div>
        
        <h3 style="color: #20364B; margin: 20px 0 10px 0;">Clés rendues :</h3>
        <div class="inspection-content">
            <div class="inspection-item">OK</div>
        </div>
        
        <h3 style="color: #20364B; margin: 20px 0 10px 0;">Observations du résident :</h3>
        <div class="inspection-content">
            <div class="inspection-item">RAS</div>
        </div>
    </div>

    <div class="solde-section">
        <h2>Solde de Tout Compte</h2>
        <div class="solde-grid">
            <div class="solde-row">
                <div class="solde-label">Redevance jusqu'à la date de sortie :</div>
                <div class="solde-value">{{ $redevance ?? '0' }} €</div>
            </div>
            <div class="solde-row">
                <div class="solde-label">Dépôt de garantie versé à l'entrée :</div>
                <div class="solde-value">{{ $depotGarantie ?? '505' }} €</div>
            </div>
            <div class="solde-row">
                <div class="solde-label">Déductions (réparations, nettoyage, etc.) :</div>
                <div class="solde-value">{{ $deductions ?? '0' }} €</div>
            </div>
            <div class="solde-row">
                <div class="solde-label">Solde CAF :</div>
                <div class="solde-value">{{ $soldeCaf ?? '0' }} €</div>
            </div>
            <div class="solde-row total-row">
                <div class="solde-label">Montant dû au résident :</div>
                <div class="solde-value">{{ $montantDuAuResident ?? '505' }} €</div>
            </div>
            <div class="solde-row total-row">
                <div class="solde-label">Montant dû par le résident :</div>
                <div class="solde-value">{{ $montantDuParResident ?? '0' }} €</div>
            </div>
            <div class="solde-row">
                <div class="solde-label">Date de règlement :</div>
                <div class="solde-value">{{ $dateReglement ?? now()->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    

    <div class="signature-section">
        <div class="signature-block">
            <h3>Signature du résident</h3>
            <div class="signature-space"></div>
            <div class="date-field">Sur la fiche d'état des lieux</div>
        </div>
        
        <div class="signature-block">
            <h3>Signature du représentant de la résidence</h3>
            <div class="signature-space"></div>
            <div class="date-field">Date : _________________</div>
        </div>
    </div>

    <div class="footer">
        <p><strong>Résidence Pré Saint Jean</strong></p>
        <p>11 Chemin du BRAY - ANNECY-LE-VIEUX - 74940 ANNECY – Siret : 338 051 899 00015</p>
        <p>Tel : 04 50 66 36 81 - Fax : 04 50 66 20 99</p>
        <p>E-mail : residence.presaintjean@wanadoo.fr - www.presaintjean.com</p>
        <p style="margin-top: 15px; font-style: italic;">Document généré automatiquement le {{ $dateGeneration }} à {{ $heureGeneration }}</p>
    </div>
</body>
<style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
            background-color: #ffffff;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #20364B;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #20364B;
            font-size: 24px;
            margin: 0;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .header .subtitle {
            color: #20364B;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .info-section {
            background-color: #f8f9fa;
            border: 1px solid #20364B;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .info-section h2 {
            color: #20364B;
            font-size: 16px;
            margin: 0 0 15px 0;
            border-bottom: 1px solid #FDC11F;
            padding-bottom: 5px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label, .info-value {
            display: table-cell;
            padding: 8px 0;
            vertical-align: top;
        }
        
        .info-label {
            font-weight: bold;
            color: #20364B;
            width: 40%;
            padding-right: 20px;
        }
        
        .info-value {
            color: #20364B;
            border-bottom: 1px solid #FDC11F;
            padding-bottom: 8px;
        }
        
        .inspection-section {
            margin-top: 30px;
            border: 2px solid #FDC11F;
            border-radius: 8px;
            padding: 20px;
            background-color: #fffcf5;
        }
        
        .inspection-section h2 {
            color: #20364B;
            font-size: 16px;
            margin: 0 0 15px 0;
            text-align: center;
        }
        
        .inspection-grid {
            display: table;
            width: 100%;
        }
        
        .inspection-row {
            display: table-row;
        }
        
        .inspection-label, .inspection-field {
            display: table-cell;
            padding: 10px 0;
            vertical-align: middle;
        }
        
        .inspection-label {
            font-weight: bold;
            color: #20364B;
            width: 50%;
            padding-right: 20px;
        }
        
        .inspection-field {
            border: 1px solid #FDC11F;
            background-color: white;
            height: 30px;
            border-radius: 4px;
        }
        
        .inspection-value {
            color: #20364B;
            font-weight: bold;
        }
        
        .inspection-content {
            margin: 10px 0;
        }
        
        .inspection-item {
            padding: 5px 0;
            color: #20364B;
        }
        
        .solde-section {
            margin-top: 30px;
            border: 2px solid #20364B;
            border-radius: 8px;
            padding: 20px;
            background-color: #f8f9fa;
        }
        
        .solde-section h2 {
            color: #20364B;
            font-size: 16px;
            margin: 0 0 15px 0;
            text-align: center;
            font-weight: bold;
        }
        
        .solde-grid {
            display: table;
            width: 100%;
        }
        
        .solde-row {
            display: table-row;
        }
        
        .solde-label, .solde-value {
            display: table-cell;
            padding: 8px 0;
            vertical-align: top;
        }
        
        .solde-label {
            font-weight: bold;
            color: #20364B;
            width: 70%;
            padding-right: 20px;
        }
        
        .solde-value {
            color: #20364B;
            font-weight: bold;
            text-align: right;
        }
        
        .total-row .solde-label,
        .total-row .solde-value {
            font-size: 14px;
            font-weight: bold;
            color: #20364B;
            border-top: 1px solid #FDC11F;
            padding: 10px 20px;
        }
        
        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        
        .signature-block {
            display: table-cell;
            width: 48%;
            text-align: center;
            padding: 20px;
            border: 1px solid #20364B;
            border-radius: 8px;
            background-color: #f8f9fa;
        }
        
        .signature-block:first-child {
            margin-right: 4%;
        }
        
        .signature-block h3 {
            color: #20364B;
            font-size: 14px;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        
        .signature-space {
            height: 60px;
            border-bottom: 1px solid #20364B;
            margin-bottom: 10px;
        }
        
        .date-field {
            margin-top: 20px;
            font-size: 11px;
            color: #CDCBCE;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #20364B;
            border-top: 1px solid #FDC11F;
            padding-top: 15px;
        }
        
        .important-note {
            background-color: #fffcf5;
            border: 1px solid #FDC11F;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            color: #20364B;
            font-weight: bold;
            text-align: center;
        }
        
        .checkbox {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 2px solid #20364B;
            margin-right: 8px;
            vertical-align: middle;
        }
    </style>
</html>
