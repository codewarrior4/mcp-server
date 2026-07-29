<?php

namespace App\MCP\Services;

use App\MCP\DTO\ToolRequestDTO;
use App\MCP\Exceptions\InvalidToolRequestException;
use Illuminate\Support\Facades\Validator;

class ToolRequestValidator
{
    public function validate(ToolRequestDTO $request): void
    {
        $validator = Validator::make([
            'tool_name' => $request->toolName,
            'parameters' => $request->parameters,
            'user_id' => $request->context->user->id,
            'user_name' => $request->context->user->name,
            'guard' => $request->context->user->guard,
        ], [
            'tool_name' => ['required', 'string'],
            'parameters' => ['required', 'array'],
            'user_id' => ['required'],
            'user_name' => ['required', 'string'],
            'guard' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            throw new InvalidToolRequestException($validator->errors()->toArray());
        }
    }
}
