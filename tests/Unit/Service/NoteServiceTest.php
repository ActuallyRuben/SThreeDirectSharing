<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: ActuallyRuben <actuallyruben@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\SThreeDirectSharing\Tests\Unit\Service;

use OCA\SThreeDirectSharing\Service\NoteNotFound;
use PHPUnit\Framework\TestCase;

use OCP\AppFramework\Db\DoesNotExistException;

use OCA\SThreeDirectSharing\Db\Note;
use OCA\SThreeDirectSharing\Service\NoteService;
use OCA\SThreeDirectSharing\Db\NoteMapper;

class NoteServiceTest extends TestCase {
	private NoteService $service;
	private string $userId = 'john';
	private $mapper;

	public function setUp(): void {
		$this->mapper = $this->getMockBuilder(NoteMapper::class)
			->disableOriginalConstructor()
			->getMock();
		$this->service = new NoteService($this->mapper);
	}

	public function testUpdate(): void {
		// the existing note
		$note = Note::fromRow([
			'id' => 3,
			'title' => 'yo',
			'content' => 'nope'
		]);
		$this->mapper->expects($this->once())
			->method('find')
			->with($this->equalTo(3))
			->will($this->returnValue($note));

		// the note when updated
		$updatedNote = Note::fromRow(['id' => 3]);
		$updatedNote->setTitle('title');
		$updatedNote->setContent('content');
		$this->mapper->expects($this->once())
			->method('update')
			->with($this->equalTo($updatedNote))
			->will($this->returnValue($updatedNote));

		$result = $this->service->update(3, 'title', 'content', $this->userId);

		$this->assertEquals($updatedNote, $result);
	}

	public function testUpdateNotFound(): void {
		$this->expectException(NoteNotFound::class);
		// test the correct status code if no note is found
		$this->mapper->expects($this->once())
			->method('find')
			->with($this->equalTo(3))
			->will($this->throwException(new DoesNotExistException('')));

		$this->service->update(3, 'title', 'content', $this->userId);
	}
}
