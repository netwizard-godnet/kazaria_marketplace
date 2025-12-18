@extends('admin.layouts.app')

@section('title', 'Détails de la Facture')

@section('content')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Facture: {{ $invoice->invoice_number }}</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item">
                <a href="{{ route('admin.invoices.index') }}">Factures</a>
            </li>
            <li class="separator"><i class="flaticon-right-arrow"></i></li>
            <li class="nav-item"><span>{{ $invoice->invoice_number }}</span></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Détails de la facture -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Détails de la Facture</h4>
                    <div class="card-tools">
                        <span class="badge bg-{{ $invoice->status_class }}">{{ $invoice->status_label }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Informations Client</h6>
                            <p><strong>Nom:</strong> {{ $invoice->client_name }}</p>
                            <p><strong>Email:</strong> {{ $invoice->client_email }}</p>
                            @if($invoice->client_phone)
                                <p><strong>Téléphone:</strong> {{ $invoice->client_phone }}</p>
                            @endif
                            @if($invoice->client_address)
                                <p><strong>Adresse:</strong> {{ $invoice->client_address }}</p>
                            @endif
                            @if($invoice->client_city)
                                <p><strong>Ville:</strong> {{ $invoice->client_city }}</p>
                            @endif
                            @if($invoice->client_postal_code)
                                <p><strong>Code postal:</strong> {{ $invoice->client_postal_code }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6>Informations Facture</h6>
                            <p><strong>N° Facture:</strong> {{ $invoice->invoice_number }}</p>
                            <p><strong>Date d'émission:</strong> {{ $invoice->invoice_date->format('d/m/Y') }}</p>
                            @if($invoice->due_date)
                                <p><strong>Date d'échéance:</strong> {{ $invoice->due_date->format('d/m/Y') }}</p>
                            @endif
                            @if($invoice->paid_date)
                                <p><strong>Date de paiement:</strong> {{ $invoice->paid_date->format('d/m/Y') }}</p>
                            @endif
                            @if($invoice->order)
                                <p><strong>Commande associée:</strong> 
                                    <a href="{{ route('admin.orders.show', $invoice->order) }}">{{ $invoice->order->order_number }}</a>
                                </p>
                            @endif
                        </div>
                    </div>

                    @if($invoice->description)
                    <div class="mb-4">
                        <h6>Description</h6>
                        <p>{{ $invoice->description }}</p>
                    </div>
                    @endif

                    <!-- Tableau des montants -->
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td><strong>Sous-total</strong></td>
                                    <td class="text-end">{{ number_format($invoice->subtotal, 0, ',', ' ') }} FCFA</td>
                                </tr>
                                @if($invoice->tax_rate > 0)
                                <tr>
                                    <td>TVA ({{ $invoice->tax_rate }}%)</td>
                                    <td class="text-end">{{ number_format($invoice->tax_amount, 0, ',', ' ') }} FCFA</td>
                                </tr>
                                @endif
                                @if($invoice->shipping_cost > 0)
                                <tr>
                                    <td>Frais de livraison</td>
                                    <td class="text-end">{{ number_format($invoice->shipping_cost, 0, ',', ' ') }} FCFA</td>
                                </tr>
                                @endif
                                @if($invoice->discount > 0)
                                <tr>
                                    <td>Remise</td>
                                    <td class="text-end text-danger">- {{ number_format($invoice->discount, 0, ',', ' ') }} FCFA</td>
                                </tr>
                                @endif
                                <tr class="table-primary">
                                    <td><strong>TOTAL</strong></td>
                                    <td class="text-end"><strong>{{ number_format($invoice->total, 0, ',', ' ') }} FCFA</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    @if($invoice->payment_method)
                    <div class="mt-4">
                        <h6>Informations de Paiement</h6>
                        <p><strong>Méthode:</strong> 
                            @if($invoice->payment_method == 'card')
                                Carte bancaire
                            @elseif($invoice->payment_method == 'mobile_money')
                                Mobile Money
                            @elseif($invoice->payment_method == 'cash')
                                Espèces
                            @elseif($invoice->payment_method == 'bank_transfer')
                                Virement bancaire
                            @else
                                {{ ucfirst(str_replace('_', ' ', $invoice->payment_method)) }}
                            @endif
                        </p>
                        @if($invoice->payment_reference)
                            <p><strong>Référence:</strong> {{ $invoice->payment_reference }}</p>
                        @endif
                        @if($invoice->payment_notes)
                            <p><strong>Notes:</strong> {{ $invoice->payment_notes }}</p>
                        @endif
                    </div>
                    @endif

                    @if($invoice->terms)
                    <div class="mt-4">
                        <h6>Conditions Générales</h6>
                        <p>{{ $invoice->terms }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Actions</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.invoices.download', $invoice) }}" class="btn btn-success">
                            <i class="fas fa-download"></i> Télécharger PDF
                        </a>
                        @can('edit_invoices')
                        <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        @endcan
                        <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>
            </div>

            <!-- Informations supplémentaires -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title">Informations</h4>
                </div>
                <div class="card-body">
                    <p><strong>Créée par:</strong> {{ $invoice->creator->prenoms ?? 'N/A' }} {{ $invoice->creator->nom ?? '' }}</p>
                    <p><strong>Créée le:</strong> {{ $invoice->created_at->format('d/m/Y H:i') }}</p>
                    @if($invoice->updated_at != $invoice->created_at)
                        <p><strong>Modifiée le:</strong> {{ $invoice->updated_at->format('d/m/Y H:i') }}</p>
                    @endif
                    @if($invoice->isOverdue())
                        <div class="alert alert-danger mt-3">
                            <i class="fas fa-exclamation-triangle"></i> Cette facture est en retard !
                        </div>
                    @endif
                </div>
            </div>

            @if($invoice->notes)
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title">Notes Internes</h4>
                </div>
                <div class="card-body">
                    <p>{{ $invoice->notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

