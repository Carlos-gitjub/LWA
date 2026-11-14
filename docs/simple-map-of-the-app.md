# Simple map of the app

Movies and Library follow Clean Architecture: **business rules in the centre;
frameworks, databases, APIs, and UI around them.** Authentication and profile
management still use conventional Laravel structure.

## The main parts

| Part | What belongs here | Where in this app |
| --- | --- | --- |
| **Domain** | General business rules and entities: what a valid book is, how occurrences in a page are counted. | `app/Domain/{Library,Movies}/Entities` |
| **Application** | What this app does: add a book, search contents, find streaming offers. Interactors coordinate the steps through interfaces. | `app/Domain/{Library,Movies}/UseCases` |
| **Interface adapters** | Controllers translate HTTP input into use-case input. Presenters translate use-case output into view models. Data adapters translate between core data and external systems. | `app/Http/Controllers`, `app/Http/Presenters`, `app/Infrastructure/*/Adapters` |
| **Frameworks and external details** | Laravel, Eloquent, database, file storage, PDF parser, provider HTTP clients, Vue UI. | `routes`, `app/Http/Requests`, `app/Infrastructure`, `resources/js`, `database` |
| **Composition** | Connect interfaces to concrete implementations using dependency injection. | `app/Providers/*ServiceProvider.php` |

These are conceptual layers, not a perfect one-folder-per-layer arrangement.
**The folder named `Domain` contains both the domain and application layers.**
Infrastructure contains both adapters and the technical details they use.

## What “independent” means

- **Domain:** the most general business policies. It does not know about use
  cases, Laravel, HTTP, databases, or Vue. It can be reused where the same
  business rules apply.
- **Application:** policies specific to this application's operations. It knows
  about domain objects and its own interfaces/data, but not concrete controllers,
  presenters, databases, or API providers. It can be reused in another delivery
  mechanism or app when the same workflows fit, with suitable adapters.
- **Outer layers:** know about the core and connect it to particular technologies.

“Higher level” means closer to business policy, not more abstract syntax or more
interfaces. Independence makes reuse possible; it does not guarantee that the
same rules and workflows suit every other app.

## The boxes in the diagrams, mapped to our code

Using `ListBooks` as the example (`I` = interface; `DS` = data structure):

| Diagram box | Our implementation | Job |
| --- | --- | --- |
| Controller | `LibraryController::index()` | Converts HTTP input into a use-case call and returns the HTTP response. |
| Input Data (`DS`) | `ListBooksInputData` | Plain input values; empty for this operation. |
| Input Boundary (`I`) | `ListBooksInputBoundary` | Declares the `execute()` operation. |
| Use Case Interactor | `ListBooksInteractor` | Implements that operation and coordinates the work. |
| Entities | Domain `Book`, `BookPage` | Business objects/rules used where the workflow needs them; listing does not need to construct them. |
| Data Access Interface (`I`) | `BookRepository` | Declares the persistence capabilities the application needs. |
| Data Access / Gateway | `EloquentBookRepository` | Implements those capabilities using Eloquent and the database. |
| Output Data (`DS`) | `ListBooksOutputData` | Carries the use-case result. |
| Output Boundary (`I`) | `ListBooksOutputBoundary` | Declares `present()` without choosing a presentation technology. |
| Presenter | `ListBooksPresenter` | Implements the output boundary and prepares display data. |
| View Model (`DS`) | `App\Http\Presenters\Library\ViewModel` | Holds the presenter's prepared data. |
| View | `resources/js/Pages/Library/Index.vue` | Displays the data and handles interaction. |

The diagrams show architectural roles; we do not implement every variation in
them. For example, this app has no separate screen and print presenters, and
the backend view model reaches Vue through JSON or Inertia props.

## One request through the app

```text
Vue → route / request validation → Controller
                                      │ InputData, through InputBoundary
                                      ▼
                                  Interactor → domain rules when needed
                                      │
                                      ├─ BookRepository interface
                                      │       → EloquentBookRepository → database
                                      │
                                      └─ OutputData, through OutputBoundary
                                              → Presenter → ViewModel
                                                              │
Vue ← JSON / Inertia response ← Controller reads ViewModel ─────┘
```

This diagram shows **runtime calls and data flow**. Source-code dependencies
follow the dependency rule: **outer code depends inward; core code never imports
the concrete outer implementations.** The interactor calls a repository or
presenter through an interface owned by the application layer.

## How the pieces are connected

```php
// LibraryServiceProvider
$this->app->bind(BookRepository::class, EloquentBookRepository::class);
$this->app->bind(ListBooksInputBoundary::class, ListBooksInteractor::class);
$this->app->scoped(ListBooksPresenter::class);
$this->app->bind(
    ListBooksOutputBoundary::class,
    fn ($app) => $app->make(ListBooksPresenter::class),
);
```

Laravel supplies the concrete objects. The scoped registration makes the
controller and interactor share the same presenter: the interactor fills its
view model, then the controller reads it.

**Remember:** entity = business rules; interactor = workflow; interface = what
the core needs; adapter = how it is done; presenter = response shape; Vue = UI;
service provider = wiring.

For more detail, see [architecture.md](architecture.md).
