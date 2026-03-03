# Rotabonita

**Package Laravel sem configuração que substitui automaticamente IDs numéricos nas URLs por tokens públicos no estilo YouTube.**

[![Latest Stable Version](https://poser.pugx.org/arnaldo-tomo/rotabonita/v/stable)](https://packagist.org/packages/arnaldo-tomo/rotabonita)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/php-%5E8.1-8892BF)](https://php.net)
[![Laravel](https://img.shields.io/badge/laravel-10%20%7C%2011-FF2D20)](https://laravel.com)

> 📖 [English Documentation](README.md)

---

## Instalação

```bash
composer require arnaldo-tomo/rotabonita
```

Publica e configura a migration para cada tabela que queres proteger:

```bash
php artisan vendor:publish --tag=rotabonita-migrations
```

Abre o ficheiro publicado em `database/migrations/` e define o nome da tua tabela:

```php
protected string $table = 'posts'; // ← muda aqui
```

Executa a migration:

```bash
php artisan migrate
```

**Terminado. Não é necessária mais nenhuma configuração.**

---

## O que muda

**Antes** de instalar o Rotabonita:
```
GET  /posts/1
GET  /posts/2
GET  /users/47
```

**Depois** de instalar o Rotabonita:
```
GET  /posts/BYPWtH2qYos
GET  /posts/K9mXpL2rTnQ
GET  /users/w4RvNcJ8ZoM
```

O teu código fica **exactamente igual**. Models, rotas, controllers, templates Blade — nada muda.

---

## Como funciona

O Rotabonita intercepta o Laravel em três pontos automaticamente:

**1 — Na criação do model**
```php
Post::create(['title' => 'Olá']); // → public_id = 'BYPWtH2qYos' atribuído automaticamente
```

**2 — Na geração de URLs**
```php
route('posts.show', $post); // → /posts/BYPWtH2qYos  (não /posts/1)
```

**3 — Na resolução da rota**
```
GET /posts/BYPWtH2qYos → SELECT * FROM posts WHERE public_id = 'BYPWtH2qYos'
GET /posts/1           → SELECT * FROM posts WHERE id = 1  (fallback)
```

Se o registo não existir → HTTP 404, igual ao comportamento padrão do Laravel.

---

## O teu código não muda

```php
// Model — igual
class Post extends Model
{
    protected $fillable = ['title'];
}

// Rota — igual
Route::resource('posts', PostController::class);

// Controller — igual
public function show(Post $post): View
{
    return view('posts.show', compact('post'));
}

// Blade — igual, mas agora gera a URL com token
route('posts.show', $post) // → /posts/BYPWtH2qYos ✅
```

---

## Formato do token

| Propriedade | Valor |
|---|---|
| Comprimento | 11 caracteres |
| Alfabeto | `A–Z a–z 0–9 _ -` (64 símbolos) |
| Total de combinações | 64¹¹ ≈ **73 quintiliões** |
| Fonte de entropia | `random_bytes()` — criptograficamente seguro |
| Unicidade | Verificada na base de dados antes de guardar |
| Protecção contra colisões | Tenta de novo até 10× numa colisão (praticamente impossível) |

---

## Preencher registos existentes

Se adicionaste `public_id` a uma tabela que já tem dados:

```bash
php artisan tinker
```

```php
$gen = app(\Rotabonita\TokenGenerator::class);

App\Models\Post::whereNull('public_id')->each(function ($post) use ($gen) {
    $post->public_id = $gen->generateUnique($post);
    $post->saveQuietly();
});
```

---

## Avançado: models fora de app/Models/

Se os teus models estão numa directoria não convencional, regista-os manualmente:

```php
// AppServiceProvider::register()
$this->app->bind('rotabonita.models', fn() => [
    \App\Domain\Blog\Post::class,
    \App\Domain\Commerce\Product::class,
]);
```

---

## Porquê usar o Rotabonita?

Por padrão, o Laravel expõe os IDs da base de dados directamente nas URLs:

```
/posts/1   /posts/2   /users/47
```

Isto é problemático porque:
- Qualquer utilizador consegue saber **quantos registos** existem
- É fácil **enumerar recursos** incrementando o número
- A aplicação parece pouco profissional

O Rotabonita resolve isto **automaticamente e de forma invisível** para o developer.

---

## Compatibilidade

| Laravel | PHP  | Estado |
|---------|------|--------|
| 10.x    | 8.1+ | ✅ Suportado |
| 11.x    | 8.2+ | ✅ Suportado |

---

## Licença

MIT © [Arnaldo Tomo](https://github.com/arnaldo-tomo)
