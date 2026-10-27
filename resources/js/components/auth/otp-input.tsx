import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { cn } from '@/lib/utils';

type Props = {
    value: string;
    onChange: (value: string) => void;
    /** Longueur du code (6 cases dans la maquette). */
    length?: number;
    disabled?: boolean;
    className?: string;
    'aria-label'?: string;
};

const SLOT_CLASS =
    'size-11 rounded-xl border border-input bg-background font-mono text-xl font-bold transition-all ' +
    'data-[active=true]:z-10 data-[active=true]:border-ring data-[active=true]:ring-[3px] data-[active=true]:ring-ring/40';

/**
 * Code OTP à 6 cases (maquette : .otp) — auto-avance, backspace et collage
 * d'un code complet gérés par input-otp. Navigation clavier native.
 */
export function OtpInput({
    value,
    onChange,
    length = 6,
    disabled,
    className,
    'aria-label': ariaLabel = "Code d'authentification à deux facteurs",
}: Props) {
    return (
        <InputOTP
            value={value}
            onChange={onChange}
            maxLength={length}
            disabled={disabled}
            aria-label={ariaLabel}
            containerClassName={cn('gap-2', className)}
        >
            <InputOTPGroup className="w-full gap-2">
                {Array.from({ length }, (_, index) => (
                    <InputOTPSlot
                        key={index}
                        index={index}
                        className={SLOT_CLASS}
                    />
                ))}
            </InputOTPGroup>
        </InputOTP>
    );
}
