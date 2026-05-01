@extends('layouts.app')

@section('title', 'Admin News | KNEWS')

@section('content')
    <div class="admin-section">
        <div class="admin-section__header">
            <h1 class="admin-section__title">ADMIN PANEL</h1>
            <div class="admin-section__actions">
                <a href="{{ route('admin.news.create') }}" class="btn-brutal">ADD NEWS</a>
                <form action="{{ route('admin.logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn-brutal btn-brutal--red">LOGOUT</button>
                </form>
            </div>
        </div>

        @if($newsList->isEmpty())
            <div class="empty-state">
                <h2 class="empty-state__title">NO ARTICLES</h2>
                <p class="empty-state__text">The pressroom is empty. Create your first dispatch.</p>
            </div>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>TITLE</th>
                            <th>CATEGORY</th>
                            <th>AUTHOR</th>
                            <th>PUBLISHED</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($newsList as $news)
                            <tr>
                                <td>{{ $news->title }}</td>
                                <td>{{ strtoupper($news->category ?? '') }}</td>
                                <td>{{ $news->author }}</td>
                                <td>{{ $news->published_at ? $news->published_at->format('Y-m-d') : 'UNPUBLISHED' }}</td>
                                <td>
                                    <a href="{{ route('news.show', $news) }}" class="btn-brutal btn-brutal--stone" style="padding:4px 10px;font-size:12px;">VIEW</a>
                                    <a href="{{ route('admin.news.edit', $news) }}" class="btn-brutal btn-brutal--stone" style="padding:4px 10px;font-size:12px;">EDIT</a>
                                    <form action="{{ route('admin.news.destroy', $news) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-brutal btn-brutal--red" style="padding:4px 10px;font-size:12px;" onclick="return confirm('Delete this article?')">DELETE</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection