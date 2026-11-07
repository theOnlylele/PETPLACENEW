<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vaccination Certificate | PetPlace</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#0f2137; --muted:#4a6080; --accent:#4f86c6; --accent-dark:#3d75b5; --border:#c8dff5; --pale:#deeaf8; }
        body { margin:0; padding:32px; color:var(--ink); font:16px/1.6 'DM Sans',sans-serif; background:#eef4fd; background-image:radial-gradient(circle at 15% 15%,rgba(79,134,198,.1) 0%,transparent 50%),radial-gradient(circle at 85% 85%,rgba(79,134,198,.06) 0%,transparent 50%); }
        .certificate { max-width:760px; margin:30px auto; padding:52px; border:8px double var(--accent); background:white; }
        .brand { color:var(--accent); font-weight:bold; letter-spacing:2px; text-transform:uppercase; }
        h1 { margin:5px 0 30px; text-align:center; font-size:32px; }
        .pet { margin:28px 0; padding:20px; background:#f5f9ff; border:1px solid var(--border); border-radius:10px; text-align:center; }
        .pet strong { display:block; font-size:25px; }
        .details { display:grid; grid-template-columns:1fr 1fr; gap:14px 28px; }
        .detail { border-bottom:1px solid var(--border); padding:7px 0; }
        .label { display:block; font-size:12px; color:var(--muted); text-transform:uppercase; }
        .signature { margin-top:60px; width:270px; border-top:1px solid var(--ink); padding-top:8px; }
        .actions { max-width:760px; margin:auto; }
        button { padding:10px 15px; border:0; border-radius:7px; color:white; background:var(--accent); cursor:pointer; font:600 14px 'DM Sans',sans-serif; }
        button:hover { background:var(--accent-dark); }
        @media print { body{padding:0;background:#fff}.certificate{margin:0 auto;border:8px double var(--accent)}.actions{display:none} }
        @media(max-width:600px) { body{padding:10px}.certificate{padding:22px}.details{grid-template-columns:1fr} }
    </style>
</head>
<body>
    <div class="actions"><button type="button" onclick="window.print()">Print certificate</button></div>
    <main class="certificate">
        <div class="brand">PetPlace Veterinary Care</div>
        <h1>Vaccination Certificate</h1>
        <p>This certifies that the following vaccination was administered or approved by a licensed veterinarian.</p>
        <div class="pet">
            <strong>{{ $record->pet?->pet_name }}</strong>
            {{ ucfirst($record->pet?->pet_type ?? 'Pet') }}
            @if($record->pet?->breed) · {{ $record->pet->breed }} @endif
            <div>Owner: {{ $record->pet?->petOwner?->first_name }} {{ $record->pet?->petOwner?->last_name }}</div>
        </div>
        <div class="details">
            <div class="detail"><span class="label">Vaccine</span>{{ $vaccineName }}</div>
            <div class="detail"><span class="label">Category</span>{{ $record->category }}</div>
            <div class="detail"><span class="label">Date given</span>{{ $record->date_given?->format('F j, Y') }}</div>
            <div class="detail"><span class="label">Next due</span>{{ $record->next_due_date?->format('F j, Y') ?: 'Not specified' }}</div>
            <div class="detail"><span class="label">Dose</span>{{ $record->dose_number ?: 'Not specified' }}</div>
            <div class="detail"><span class="label">Brand</span>{{ $record->brand ?: 'Not specified' }}</div>
            <div class="detail"><span class="label">Batch / lot number</span>{{ $record->batch_number ?: 'Not specified' }}</div>
            <div class="detail"><span class="label">Veterinarian</span>Dr. {{ $record->veterinarian?->first_name }} {{ $record->veterinarian?->last_name }}</div>
        </div>
        @if($record->notes)<p><strong>Notes:</strong> {{ $record->notes }}</p>@endif
        <div class="signature">Veterinarian signature</div>
    </main>
</body>
</html>
