<?php

namespace App\Livewire;

use Exception;
use App\Models\Todo;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Rule;

class TodoList extends Component
{
    use WithPagination;

    #[Rule('required|min:3|max:50')]
    public $name;

    public $search;
    public $editingTodoId;
    #[Rule('required|min:3|max:50')]
    public $editingTodoName;


    public function create()
    {
        $validated = $this->validateOnly('name');
        Todo::create($validated);
        $this->reset('name');
        session()->flash('success', 'Created');
        $this->resetPage();
    }

    public function delete($todoId)
    {
        try {
            Todo::findOrFail($todoId)->delete();
        } catch (Exception $e) {
            session()->flash('error', 'Failed to delete todo!');
            return;
        }
    }

    public function toggle(Todo $todo)
    {
        $todo->completed = !$todo->completed;
        $todo->save();
    }

    public function edit($todoId)
    {
        $this->editingTodoId = $todoId;
        $this->editingTodoName = Todo::findOrFail($todoId)->name;
    }

    public function cancelEdit()
    {
        $this->reset('editingTodoId', 'editingTodoName');
    }

    public function update()
    {
        $this->validateOnly('editingTodoName');
        Todo::find($this->editingTodoId)->update([
            'name' => $this->editingTodoName
        ]);
        $this->cancelEdit();
    }
    public function render()
    {

        return view('livewire.todo-list', [
            'todos' => Todo::latest()->where('name', 'like', "%{$this->search}%")->paginate(5),
        ]);
    }
}