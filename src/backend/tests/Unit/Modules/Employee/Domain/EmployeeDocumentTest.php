<?php

namespace Tests\Unit\Modules\Employee\Domain;

use App\Modules\Employee\Domain\Aggregates\Employee\EmployeeId;
use App\Modules\Employee\Domain\Aggregates\EmployeeDocument\DocumentDescriptor;
use App\Modules\Employee\Domain\Aggregates\EmployeeDocument\DocumentStatus;
use App\Modules\Employee\Domain\Aggregates\EmployeeDocument\EmployeeDocument;
use App\Modules\Employee\Domain\Aggregates\EmployeeDocument\EmployeeDocumentId;
use Tests\TestCase;

class EmployeeDocumentTest extends TestCase
{
    public function test_upload_and_replace_document(): void
    {
        $document = EmployeeDocument::upload(
            EmployeeDocumentId::generate(),
            EmployeeId::generate(),
            'id_card',
            new DocumentDescriptor('old.pdf', 'old.pdf', 'application/pdf', 1),
        );
        $document->releaseEvents();

        $replacement = $document->replace(
            EmployeeDocumentId::generate(),
            new DocumentDescriptor('new.pdf', 'new.pdf', 'application/pdf', 2),
        );

        $this->assertSame(DocumentStatus::Archived, $document->status());
        $this->assertSame(DocumentStatus::Active, $replacement->status());
        $this->assertCount(1, $replacement->releaseEvents());
    }
}
