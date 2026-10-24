<?php

declare(strict_types=1);

namespace Tests\Unit;

use Models\Post;
use Models\Profile;
use Models\Tag;
use Models\User;
use PHPUnit\Framework\TestCase;
use Resources\Database;
use Resources\RunException;

/**
 * Models live in tests/Fixtures/app/Models and use the 'ar' connection.
 */
final class ActiveRecordTest extends TestCase
{
    protected function setUp(): void
    {
        $db = new Database('ar');

        foreach (['users', 'post', 'profile', 'tag', 'post_tag'] as $table) {
            $db->query('DROP TABLE IF EXISTS '.$table);
        }

        $db->query('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, score INTEGER)');
        $db->query('CREATE TABLE post (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, title TEXT)');
        $db->query('CREATE TABLE profile (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, bio TEXT)');
        $db->query('CREATE TABLE tag (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT)');
        $db->query('CREATE TABLE post_tag (post_id INTEGER, tag_id INTEGER)');

        new User(['name' => 'Budi', 'email' => 'budi@example.com', 'score' => 10]);
        new User(['name' => 'Ani', 'email' => 'ani@example.com', 'score' => 20]);
        new User(['name' => 'Citra', 'email' => 'citra@example.com', 'score' => 30]);
    }

    private function rows(string $table): int
    {
        return (int) (new Database('ar'))->select('COUNT(*)')->from($table)->getVar();
    }

    public function testSaveInsertsOnceThenUpdatesTheSameRecord(): void
    {
        $user = new User();
        $user->name = 'Dewi';
        $user->email = 'dewi@example.com';

        $id = $user->save();
        $this->assertEquals(4, $id);
        $this->assertEquals(4, $user->id);

        $user->name = 'Dewi Lestari';
        $user->save();

        $this->assertSame(4, $this->rows('users'));
        $this->assertSame('Dewi Lestari', (new User())->get(4)->name);
    }

    public function testConstructorWithPrimaryKeyUpdates(): void
    {
        new User(['id' => 2, 'name' => 'Ani Yudhoyono']);

        $this->assertSame('Ani Yudhoyono', (new User())->get(2)->name);
        $this->assertSame(3, $this->rows('users'));
    }

    public function testGetByPrimaryKey(): void
    {
        $user = new User();

        $row = $user->get(2);
        $this->assertSame('Ani', $row->name);
        $this->assertSame('Ani', $user->name, 'get(id) fills the model');

        $this->assertCount(2, $user->get(1, 3));
        $this->assertFalse($user->get(99));
    }

    public function testDynamicFinders(): void
    {
        $user = new User();

        $this->assertSame('Ani', $user->getByEmail('ani@example.com')->name);
        $this->assertEquals(2, $user->id);
        $this->assertSame('Budi', $user->first()->name);
        $this->assertSame('Citra', $user->last()->name);
        $this->assertFalse($user->getByEmail('nobody@example.com'));
    }

    public function testUnknownMethodIsAnError(): void
    {
        $this->expectException(RunException::class);
        $this->expectExceptionMessage('undefined method');

        (new User())->findEverything();
    }

    public function testQueryPartsDoNotLeakIntoTheNextQuery(): void
    {
        $user = new User();

        $top = $user->condition('score', '>', 10)->order('score', 'DESC')->limit(1)->get();
        $this->assertCount(1, $top);
        $this->assertSame('Citra', $top[0]->name);

        $this->assertCount(3, $user->get());
        $this->assertSame(['Ani'], array_column($user->select('name')->group('name')->condition('score', '=', 20)->get(), 'name'));
    }

    public function testDeleteAndUpdate(): void
    {
        $user = new User();
        $user->get(1);
        $user->delete();
        $this->assertFalse((new User())->get(1));

        $user = new User();
        $user->name = 'Changed';
        $user->update(2);
        $this->assertSame('Changed', (new User())->get(2)->name);
        $this->assertSame('Citra', (new User())->get(3)->name);

        (new User())->condition('score', '>', 25)->delete();
        $this->assertSame(1, $this->rows('users'));
    }

    public function testDeleteWithoutConditionIsRefused(): void
    {
        $this->expectException(RunException::class);
        $this->expectExceptionMessage('without a WHERE');

        (new User())->delete();
    }

    public function testValuesAreBound(): void
    {
        $payload = "x' OR '1'='1";

        $this->assertFalse((new User())->getByName($payload));

        new User(['name' => $payload]);
        $this->assertSame($payload, (new User())->getByName($payload)->name);
    }

    public function testRelations(): void
    {
        new Post(['user_id' => 1, 'title' => 'Halo']);
        new Post(['user_id' => 1, 'title' => 'Dunia']);
        new Profile(['user_id' => 1, 'bio' => 'Programmer']);
        new Tag(['name' => 'php']);
        new Tag(['name' => 'kiss']);
        (new Database('ar'))->insert('post_tag', ['post_id' => 1, 'tag_id' => 2]);

        $user = new User();
        $user->get(1);
        $this->assertCount(2, $user->posts->get());
        $this->assertSame('Programmer', $user->profile->bio);

        $post = new Post();
        $post->get(1);
        $this->assertSame('Budi', $post->user->name);
        $this->assertSame(['kiss'], array_column($post->tags->get(), 'name'));

        // Rows of a model with relations are model objects, every time.
        $posts = (new Post())->get();
        $this->assertInstanceOf(Post::class, $posts[1]);
        $this->assertSame('Budi', $posts[1]->user->name);
        $this->assertInstanceOf(Post::class, (new Post())->get()[0]);
    }
}
